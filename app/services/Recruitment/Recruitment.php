<?php
declare(strict_types=1);

namespace App\Services\Recruitment;

use App\Core\Database;
use App\Core\ImageOptimizer;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Uploader;
use App\Core\UploadException;

/**
 * RECRUITMENT for the three ways people apply to us: AMBASSADOR applications, JOB applications (vacancies, including volunteer vacancies) and VOLUNTEER sign-ups (the Involvement form).
 *  - Every application gets a tracking number when it is submitted (AMB-2026-0007, JOB-..., VOL-...); the applicant can check the stage at /track-application.
 *  - Every status change e-mails the applicant (received, under review, shortlisted, invited for interview, accepted / onboarding, on hold, declined) and is written to a history.
 *  - Administrators judge an application with a click: they set the KPIs of each role (Admin > Recruitment criteria) and score each KPI 0-5. The weighted score is a percentage; at
 *    70% (setting recruit_interview_pct) the applicant is invited for interview automatically. An administrator can still accept an applicant at any score.
 *  - A CV and a portrait are required on every form; both are stored privately (storage/private/cv and storage/private/recruit/portraits).
 * The tables and columns are created on first use (install()); the same statements are in database/updates/2026-11-recruitment.sql.
 */
final class Recruitment
{
    public const MAX_POINTS = 5;

    /** scope => table, ref prefix, labels, the status that means "accepted" and "declined", the admin page of one row. */
    public const SCOPES = [
        'ambassador' => ['table' => 'ambassador_applications', 'prefix' => 'AMB', 'noun' => 'ambassador application', 'accept' => 'approved', 'decline' => 'declined', 'admin' => 'admin/ambassadors/', 'list' => 'admin/ambassadors', 'title' => 'Ambassadors'],
        'job'        => ['table' => 'applications', 'prefix' => 'JOB', 'noun' => 'job application', 'accept' => 'hired', 'decline' => 'rejected', 'admin' => 'admin/applications/', 'list' => 'admin/applications', 'title' => 'Job applications'],
        'volunteer'  => ['table' => 'involvement_requests', 'prefix' => 'VOL', 'noun' => 'volunteer application', 'accept' => 'accepted', 'decline' => 'declined', 'admin' => 'admin/involvement/', 'list' => 'admin/involvement', 'title' => 'Volunteers'],
    ];

    /** status => label, per scope (the order of the drop-down). */
    public const STATUS_LABELS = [
        'ambassador' => ['new' => 'Received', 'reviewing' => 'Under review', 'interview' => 'Invited for interview', 'approved' => 'Approved (onboarding)', 'on_hold' => 'On hold', 'declined' => 'Declined'],
        'job'        => ['new' => 'Received', 'reviewing' => 'Under review', 'shortlisted' => 'Shortlisted', 'interview' => 'Invited for interview', 'hired' => 'Hired (onboarding)', 'rejected' => 'Declined'],
        'volunteer'  => ['new' => 'Received', 'reviewing' => 'Under review', 'interview' => 'Invited for interview', 'accepted' => 'Accepted (onboarding)', 'on_hold' => 'On hold', 'declined' => 'Declined',
                         'contacted' => 'Contacted', 'in_progress' => 'In progress', 'closed' => 'Closed'],
    ];

    /** Criteria that need a person (reading the CV, the interview). The ones the system scores from the form are in AutoScore::KEYS. */
    private const DEFAULT_KPIS = [
        'ambassador' => [['Interview: commitment and credibility', 3]],
        'job'        => [['Relevant experience (read the CV)', 3], ['Qualifications and skills for the role (read the CV)', 3], ['Interview: communication and professionalism', 2]],
        'volunteer'  => [['Skills they can offer (read the CV)', 2], ['Experience and availability (read the CV)', 2], ['Interview: reliability and fit with our values', 2]],
    ];

    private static ?bool $ready = null;

    // ------------------------------------------------------------------------------------------------------------- schema

    public static function ready(): bool
    {
        if (self::$ready === null) {
            try {
                $t = (int) Database::value("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('recruit_kpis','recruit_scores','recruit_log','recruit_feedback')", [], 0);
                $a = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'recruit_kpis' AND column_name = 'auto_key'", [], 0);
                $c = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND column_name = 'ref_no' AND table_name IN ('ambassador_applications','applications','involvement_requests')", [], 0);
                $p = (int) Database::value("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ambassador_applications' AND column_name = 'profile_token'", [], 0);
                self::$ready = $t === 4 && $c === 3 && $p === 1 && $a === 1;
            } catch (\Throwable) {
                self::$ready = false;
            }
        }
        return self::$ready;
    }

    /** Create what is missing (safe to repeat). @return bool ready afterwards */
    public static function install(): bool
    {
        if (self::ready()) {
            return true;
        }
        try {
            Database::execute("CREATE TABLE IF NOT EXISTS `recruit_kpis` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `label` VARCHAR(190) NOT NULL, `weight` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0, `is_active` TINYINT(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id`), KEY `ix_recruit_kpis_scope` (`scope`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            Database::execute("CREATE TABLE IF NOT EXISTS `recruit_scores` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `kpi_id` INT UNSIGNED NOT NULL, `points` TINYINT UNSIGNED NOT NULL,
  `scored_by` INT UNSIGNED NULL, `scored_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uq_recruit_scores` (`scope`, `application_id`, `kpi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            Database::execute("CREATE TABLE IF NOT EXISTS `recruit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `status` VARCHAR(20) NOT NULL, `note` VARCHAR(600) NULL,
  `mailed` TINYINT(1) NOT NULL DEFAULT 0, `by_user` INT UNSIGNED NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `ix_recruit_log` (`scope`, `application_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            Database::execute("CREATE TABLE IF NOT EXISTS `recruit_feedback` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `scope` VARCHAR(12) NOT NULL, `application_id` INT UNSIGNED NOT NULL, `feedback` TEXT NULL, `updated_by` INT UNSIGNED NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uq_recruit_feedback` (`scope`, `application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            foreach ([['recruit_kpis', 'auto_key', 'VARCHAR(30) NULL'], ['recruit_scores', 'auto', 'TINYINT(1) NOT NULL DEFAULT 0'], ['recruit_scores', 'skipped', 'TINYINT(1) NOT NULL DEFAULT 0'], ['recruit_scores', 'comment', 'VARCHAR(300) NULL']] as [$tb, $col, $def]) {
                if ((int) Database::value('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?', [$tb, $col], 0) === 0) {
                    Database::execute("ALTER TABLE `{$tb}` ADD COLUMN `{$col}` {$def}");
                }
            }
            foreach (self::SCOPES as $scope => $cfg) {
                $t = $cfg['table'];
                if (!Database::tableExists($t)) {
                    continue;
                }
                $cols = ['ref_no' => 'VARCHAR(30) NULL', 'score_pct' => 'DECIMAL(5,2) NULL', 'scored_at' => 'DATETIME NULL', 'photo_path' => 'VARCHAR(255) NULL'];
                if ($scope === 'ambassador') {
                    $cols['profile_token'] = 'VARCHAR(64) NULL';
                }
                if ($scope !== 'job') {
                    $cols += ['cv_path' => 'VARCHAR(255) NULL', 'cv_original_name' => 'VARCHAR(190) NULL', 'cv_mime' => 'VARCHAR(100) NULL', 'cv_size' => 'INT UNSIGNED NULL'];
                }
                foreach ($cols as $col => $def) {
                    if ((int) Database::value('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?', [$t, $col], 0) === 0) {
                        Database::execute("ALTER TABLE `{$t}` ADD COLUMN `{$col}` {$def}");
                    }
                }
                $type = (string) Database::value("SELECT column_type FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = 'status'", [$t], '');
                if ($type !== '' && !str_contains($type, "'interview'")) {
                    $enum = implode(',', array_map(static fn(string $s): string => "'" . $s . "'", array_keys(self::STATUS_LABELS[$scope] + array_fill_keys(self::existingStatuses($type), ''))));
                    Database::execute("ALTER TABLE `{$t}` MODIFY `status` ENUM({$enum}) NOT NULL DEFAULT 'new'");
                }
                if ((int) Database::value("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = 'ix_{$t}_ref'", [$t], 0) === 0) {
                    Database::execute("ALTER TABLE `{$t}` ADD KEY `ix_{$t}_ref` (`ref_no`)");
                }
                if ($scope === 'ambassador') {
                    Database::execute("UPDATE `{$t}` SET profile_token = SHA2(CONCAT(id, UUID(), RAND()), 256) WHERE profile_token IS NULL");
                    if ((int) Database::value("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = 'uq_ambassador_profile_token'", [$t], 0) === 0) {
                        Database::execute("ALTER TABLE `{$t}` ADD UNIQUE KEY `uq_ambassador_profile_token` (`profile_token`)");
                    }
                }
                Database::execute("UPDATE `{$t}` SET ref_no = CONCAT(?, '-', YEAR(created_at), '-', LPAD(id, 4, '0')) WHERE ref_no IS NULL", [$cfg['prefix']]);
                if ((int) Database::value('SELECT COUNT(*) FROM recruit_kpis WHERE scope = ?', [$scope], 0) === 0) {
                    foreach (self::DEFAULT_KPIS[$scope] as $i => [$label, $w]) {
                        Database::insert('recruit_kpis', ['scope' => $scope, 'label' => $label, 'weight' => $w, 'sort_order' => 100 + ($i + 1) * 10]);
                    }
                }
                self::ensureAutoKpis($scope);
            }
        } catch (\Throwable $e) {
            Log::warning('Recruitment::install failed - ' . $e->getMessage());
        }
        self::$ready = null;
        return self::ready();
    }

    /** @return list<string> the values of a column_type like enum('a','b') */
    private static function existingStatuses(string $type): array
    {
        return preg_match_all("/'([^']+)'/", $type, $m) > 0 ? $m[1] : [];
    }

    // ------------------------------------------------------------------------------------------------------------- numbers and rows

    public static function threshold(): int
    {
        return max(1, min(100, (int) \App\Core\Settings::get('recruit_interview_pct', '70')));
    }

    /** Give a freshly inserted application its tracking number. @return string the number ('' when the columns are missing) */
    public static function assignRef(string $scope, int $id): string
    {
        if (!isset(self::SCOPES[$scope]) || !self::ready()) {
            return '';
        }
        $ref = sprintf('%s-%s-%04d', self::SCOPES[$scope]['prefix'], date('Y'), $id);
        $upd = ['ref_no' => $ref];
        if ($scope === 'ambassador') {
            $upd['profile_token'] = bin2hex(random_bytes(24));
        }
        Database::update(self::SCOPES[$scope]['table'], $upd, 'id = ?', [$id]);
        return $ref;
    }

    /** The private address of an ambassador's profile (the secret is in the link, which is only e-mailed to the applicant). */
    public static function profileUrl(array $row): string
    {
        $t = trim((string) ($row['profile_token'] ?? ''));
        return $t !== '' ? abs_url('ambassador/profile/' . $t) : '';
    }

    /** @return array<string,mixed>|null the ambassador application that owns this profile link */
    public static function rowByToken(string $token): ?array
    {
        if (!self::ready() || preg_match('/^[a-f0-9]{40,64}$/', $token) !== 1) {
            return null;
        }
        return Database::one('SELECT * FROM ambassador_applications WHERE profile_token = ?', [$token]);
    }

    /** @return array<string,mixed>|null */
    public static function row(string $scope, int $id): ?array
    {
        if (!isset(self::SCOPES[$scope])) {
            return null;
        }
        $t = self::SCOPES[$scope]['table'];
        $sel = $scope === 'job' ? 'SELECT a.*, j.title AS job_title FROM applications a LEFT JOIN jobs j ON j.id = a.job_id WHERE a.id = ?' : "SELECT * FROM `{$t}` WHERE id = ?";
        return Database::one($sel, [$id]);
    }

    /** @return array<string,string> status => label for the drop-down of this scope */
    public static function statuses(string $scope): array
    {
        return self::STATUS_LABELS[$scope] ?? [];
    }

    // ------------------------------------------------------------------------------------------------------------- files

    /** What is wrong with a portrait (checked before anything is saved); '' when it is fine. @param array<string,mixed>|null $file */
    public static function portraitProblem(?array $file): string
    {
        return \App\Services\Academy\LearnerPortrait::problem($file);
    }

    /** Save the portrait privately, square. @param array<string,mixed> $file @return array{ok:bool,path:string,error:string} */
    public static function storePortrait(array $file): array
    {
        $dir = 'recruit/portraits';
        $rel = $dir . '/' . bin2hex(random_bytes(16)) . '.jpg';
        $abs = STORAGE_PATH . '/private/' . $rel;
        if (!is_dir(dirname($abs))) {
            @mkdir(dirname($abs), 0750, true);
        }
        try {
            $res = ImageOptimizer::portrait((string) $file['tmp_name'], $abs, ['min_side' => 300, 'max_bytes' => 5 * 1024 * 1024, 'side' => 600]);
        } catch (UploadException $x) {
            return ['ok' => false, 'path' => '', 'error' => $x->getMessage()];
        }
        if ($res['ext'] !== 'jpg') {
            $rel2 = $dir . '/' . bin2hex(random_bytes(16)) . '.' . $res['ext'];
            if (!@rename($abs, STORAGE_PATH . '/private/' . $rel2)) {
                @unlink($abs);
                return ['ok' => false, 'path' => '', 'error' => 'The server could not save the picture.'];
            }
            $rel = $rel2;
        }
        return ['ok' => true, 'path' => $rel, 'error' => ''];
    }

    /** Stream a stored file of an application (portrait or CV) to an administrator, or null. */
    public static function file(string $scope, int $id, string $part): ?\App\Core\Response
    {
        $row = self::row($scope, $id);
        if ($row === null) {
            return null;
        }
        $rel = trim((string) ($row[$part === 'portrait' ? 'photo_path' : 'cv_path'] ?? ''));
        $okDir = $part === 'portrait' ? 'recruit/portraits/' : 'cv/';
        if ($rel === '' || !str_starts_with($rel, $okDir) || str_contains($rel, '..') || str_contains($rel, chr(92))) {
            return null;
        }
        try {
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if ($part === 'portrait') {
                $mime = ['png' => 'image/png', 'webp' => 'image/webp'][$ext] ?? 'image/jpeg';
                return Uploader::download($rel, 'portrait-' . $id . '.' . $ext, true)->header('Content-Type', $mime)->header('Cache-Control', 'private, max-age=300');
            }
            $slug = \App\Core\Slug::make((string) $row['name'], 60) ?: 'applicant-' . $id;
            return Uploader::download($rel, 'CV-' . $slug . '.' . $ext);
        } catch (\Throwable) {
            return null;
        }
    }

    // ------------------------------------------------------------------------------------------------------------- KPIs and judging

    /** @return list<array<string,mixed>> the criteria of a role */
    public static function kpis(string $scope, bool $activeOnly = true): array
    {
        if (!self::ready()) {
            return [];
        }
        return Database::all('SELECT * FROM recruit_kpis WHERE scope = ?' . ($activeOnly ? ' AND is_active = 1' : '') . ' ORDER BY sort_order, id', [$scope]);
    }

    public static function saveKpi(string $scope, int $id, string $label, int $weight, int $sort, bool $active): void
    {
        $label = mb_substr(trim($label), 0, 190);
        if ($label === '' || !isset(self::SCOPES[$scope])) {
            return;
        }
        $data = ['label' => $label, 'weight' => max(1, min(10, $weight)), 'sort_order' => $sort, 'is_active' => $active ? 1 : 0];
        if ($id > 0) {
            Database::update('recruit_kpis', $data, 'id = ? AND scope = ?', [$id, $scope]);
        } else {
            Database::insert('recruit_kpis', $data + ['scope' => $scope]);
        }
    }

    public static function deleteKpi(int $id): void
    {
        Database::execute('DELETE FROM recruit_scores WHERE kpi_id = ?', [$id]);
        Database::execute('DELETE FROM recruit_kpis WHERE id = ?', [$id]);
    }

    /** The criteria the system scores from the form: the old default labels become them, and any that is missing is added (an administrator may then switch them off or delete them). */
    public static function ensureAutoKpis(string $scope): void
    {
        foreach (AutoScore::OLD_LABELS as $label => $key) {
            if (AutoScore::KEYS[$key][2] === $scope) {
                Database::execute('UPDATE recruit_kpis SET auto_key = ?, label = ? WHERE scope = ? AND label = ? AND auto_key IS NULL', [$key, AutoScore::KEYS[$key][0], $scope, $label]);
            }
        }
        foreach (AutoScore::keysFor($scope) as $i => $key) {
            if ((int) Database::value('SELECT COUNT(*) FROM recruit_kpis WHERE scope = ? AND auto_key = ?', [$scope, $key], 0) === 0) {
                Database::insert('recruit_kpis', ['scope' => $scope, 'label' => AutoScore::KEYS[$key][0], 'weight' => AutoScore::KEYS[$key][1], 'sort_order' => 10 + $i * 10, 'auto_key' => $key]);
            }
        }
    }

    /** @return array<int,array<string,mixed>> kpi_id => the score row (points, auto, skipped, comment) */
    public static function scoreRows(string $scope, int $id): array
    {
        $out = [];
        if (self::ready()) {
            foreach (Database::all('SELECT * FROM recruit_scores WHERE scope = ? AND application_id = ?', [$scope, $id]) as $r) {
                $out[(int) $r['kpi_id']] = $r;
            }
        }
        return $out;
    }

    /**
     * The state of the judging: the criteria in use for this applicant (not switched off), their points, the percentage and whether every one is scored.
     * @return array{kpis:list<array<string,mixed>>,points:array<int,int>,pct:?float,complete:bool}
     */
    public static function evaluate(string $scope, int $id): array
    {
        $rows = self::scoreRows($scope, $id);
        $used = array_values(array_filter(self::kpis($scope), static fn(array $k): bool => empty($rows[(int) $k['id']]['skipped'])));
        $points = [];
        foreach ($used as $k) {
            if (isset($rows[(int) $k['id']])) {
                $points[(int) $k['id']] = (int) $rows[(int) $k['id']]['points'];
            }
        }
        return ['kpis' => $used, 'points' => $points, 'pct' => self::percent($used, $points), 'complete' => $used !== [] && count($points) === count($used)];
    }

    /** Score the criteria the system can judge from the form (a point the judge has changed by hand is never overwritten). The percentage is only saved: nobody is invited by this. */
    public static function autoScore(string $scope, int $id): void
    {
        if (!self::ready()) {
            return;
        }
        $row = self::row($scope, $id);
        if ($row === null) {
            return;
        }
        $have = self::scoreRows($scope, $id);
        foreach (self::kpis($scope) as $k) {
            $key = (string) ($k['auto_key'] ?? '');
            $kid = (int) $k['id'];
            if ($key === '' || (isset($have[$kid]) && !(int) $have[$kid]['auto'])) {
                continue;
            }
            $res = AutoScore::points($key, $row);
            if ($res === null) {
                continue;
            }
            Database::execute('INSERT INTO recruit_scores (scope, application_id, kpi_id, points, auto, scored_by) VALUES (?,?,?,?,1,NULL) ON DUPLICATE KEY UPDATE points = VALUES(points), auto = 1',
                [$scope, $id, $kid, $res[0]]);
        }
        $e = self::evaluate($scope, $id);
        Database::update(self::SCOPES[$scope]['table'], ['score_pct' => $e['pct'], 'scored_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }

    /** Score an older application that has no automatic points yet (called when the judging panel is shown). */
    public static function ensureAuto(string $scope, array $row): void
    {
        if (!self::ready()) {
            return;
        }
        $have = self::scoreRows($scope, (int) $row['id']);
        foreach (self::kpis($scope) as $k) {
            if (($k['auto_key'] ?? '') !== '' && !isset($have[(int) $k['id']])) {
                self::autoScore($scope, (int) $row['id']);
                return;
            }
        }
    }

    public static function feedback(string $scope, int $id): string
    {
        return self::ready() ? (string) Database::value('SELECT feedback FROM recruit_feedback WHERE scope = ? AND application_id = ?', [$scope, $id], '') : '';
    }

    public static function saveFeedback(string $scope, int $id, string $text, ?int $userId): void
    {
        Database::execute('INSERT INTO recruit_feedback (scope, application_id, feedback, updated_by) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE feedback = VALUES(feedback), updated_by = VALUES(updated_by)',
            [$scope, $id, mb_substr(trim($text), 0, 4000), $userId]);
    }

    /** E-mail the feedback to the applicant. @return bool|null */
    public static function sendFeedback(string $scope, array $row, string $text): ?bool
    {
        $text = trim($text);
        if ($text === '' || !Mailer::validEmail((string) ($row['email'] ?? ''))) {
            return null;
        }
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $first = explode(' ', trim((string) $row['name']))[0] ?: 'there';
        $ref = (string) ($row['ref_no'] ?? '');
        $link = $scope === 'ambassador' && self::profileUrl($row) !== '' ? self::profileUrl($row) : abs_url('track-application');
        try {
            return Mailer::send((string) $row['email'], 'Feedback on your application' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                "Hello {$first},\n\nThank you for your application" . ($ref !== '' ? ' ' . $ref : '') . ". Our team would like to share this feedback with you:\n\n{$text}\n\nFollow your application: {$link}\n\nWith thanks,\n{$site}\n");
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<int,int> kpi_id => points (criteria switched off for this applicant are left out) */
    public static function scores(string $scope, int $id): array
    {
        $out = [];
        if (self::ready()) {
            foreach (Database::all('SELECT kpi_id, points FROM recruit_scores WHERE scope = ? AND application_id = ? AND skipped = 0', [$scope, $id]) as $r) {
                $out[(int) $r['kpi_id']] = (int) $r['points'];
            }
        }
        return $out;
    }

    /** Weighted score in percent of the scored criteria, or null when none is scored. @param list<array<string,mixed>> $kpis @param array<int,int> $points */
    public static function percent(array $kpis, array $points): ?float
    {
        $sum = 0;
        $max = 0;
        foreach ($kpis as $k) {
            if (isset($points[(int) $k['id']])) {
                $sum += $points[(int) $k['id']] * (int) $k['weight'];
                $max += self::MAX_POINTS * (int) $k['weight'];
            }
        }
        return $max > 0 ? round($sum * 100 / $max, 2) : null;
    }

    /**
     * Save the judge's clicks (kpi_id => points 0-5), the criteria switched off for this applicant, the comments and the feedback, work out the percentage and, when EVERY criterion in
     * use is scored and the percentage reaches the interview level, invite the applicant to an interview (once: only from the first stages) and e-mail them.
     * $opts: use (kpi_id => 1 for the criteria that stay on; when the key is present the others are switched off), comments (kpi_id => text), feedback (text), send_feedback (bool).
     * @param array<int,mixed> $points
     * @param array<string,mixed> $opts
     * @return array{pct:?float,complete:bool,invited:bool,mailed:?bool,feedback_mailed:?bool}
     */
    public static function judge(string $scope, int $id, array $points, ?int $userId, array $opts = []): array
    {
        $kpis = self::kpis($scope);
        $rows = self::scoreRows($scope, $id);
        $useGiven = array_key_exists('use', $opts);
        foreach ($kpis as $k) {
            $kid = (int) $k['id'];
            $cur = $rows[$kid] ?? null;
            $skipped = $useGiven ? (empty($opts['use'][$kid]) ? 1 : 0) : (int) ($cur['skipped'] ?? 0);
            $has = array_key_exists($kid, $points) && $points[$kid] !== '' && $points[$kid] !== null;
            $p = $has ? max(0, min(self::MAX_POINTS, (int) $points[$kid])) : (int) ($cur['points'] ?? 0);
            $auto = $cur !== null ? (int) $cur['auto'] : 0;
            if ($has && $cur !== null && (int) $cur['points'] !== $p) {
                $auto = 0;                                                    // the judge changed a suggested point: it is theirs now
            }
            $comment = array_key_exists('comments', $opts) ? mb_substr(trim((string) ($opts['comments'][$kid] ?? '')), 0, 300) : ($cur['comment'] ?? null);
            if (!$has && $cur === null && !$skipped && $comment === null) {
                continue;                                                     // nothing to store for a criterion nobody touched
            }
            if ($cur === null && !$has) {
                $p = 0;
            }
            Database::execute('INSERT INTO recruit_scores (scope, application_id, kpi_id, points, auto, skipped, comment, scored_by) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE points = VALUES(points), auto = VALUES(auto), skipped = VALUES(skipped), comment = VALUES(comment), scored_by = VALUES(scored_by), scored_at = NOW()',
                [$scope, $id, $kid, $p, $auto, $skipped, $comment !== '' ? $comment : null, $userId]);
        }
        $e = self::evaluate($scope, $id);
        $pct = $e['pct'];
        $complete = $e['complete'];
        Database::update(self::SCOPES[$scope]['table'], ['score_pct' => $pct, 'scored_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
        $row = self::row($scope, $id);
        $fbMailed = null;
        if (array_key_exists('feedback', $opts)) {
            self::saveFeedback($scope, $id, (string) $opts['feedback'], $userId);
            if (!empty($opts['send_feedback']) && $row !== null) {
                $fbMailed = self::sendFeedback($scope, $row, (string) $opts['feedback']);
            }
        }
        $invited = false;
        $mailed = null;
        if ($complete && $pct !== null && $pct >= self::threshold() && $row !== null && in_array((string) $row['status'], ['new', 'reviewing', 'shortlisted', 'contacted', 'in_progress'], true)) {
            $r = self::setStatus($scope, $id, 'interview', 'Scored ' . rtrim(rtrim(number_format($pct, 1), '0'), '.') . '% (interview level: ' . self::threshold() . '%)', $userId, '');
            $invited = $r['ok'];
            $mailed = $r['mailed'];
        }
        return ['pct' => $pct, 'complete' => $complete, 'invited' => $invited, 'mailed' => $mailed, 'feedback_mailed' => $fbMailed];
    }

    // ------------------------------------------------------------------------------------------------------------- status and e-mails

    /**
     * Change the status, write it to the history and e-mail the applicant (every stage has its own message). $note is added to the e-mail when given ($logNote goes to the history).
     * @return array{ok:bool,changed:bool,mailed:?bool}
     */
    public static function setStatus(string $scope, int $id, string $to, string $logNote, ?int $userId, string $applicantNote, bool $notify = true): array
    {
        if (!isset(self::SCOPES[$scope]) || !isset(self::STATUS_LABELS[$scope][$to])) {
            return ['ok' => false, 'changed' => false, 'mailed' => null];
        }
        $row = self::row($scope, $id);
        if ($row === null) {
            return ['ok' => false, 'changed' => false, 'mailed' => null];
        }
        $changed = (string) $row['status'] !== $to;
        if (!$changed) {
            return ['ok' => true, 'changed' => false, 'mailed' => null];
        }
        $upd = ['status' => $to];
        if ($scope === 'ambassador') {
            $upd += ['reviewed_by' => $userId, 'reviewed_at' => date('Y-m-d H:i:s')];
        }
        Database::update(self::SCOPES[$scope]['table'], $upd, 'id = ?', [$id]);
        $mailed = null;
        if ($notify) {
            $row['status'] = $to;
            $mailed = self::notify($scope, $row, $to, $applicantNote);
        }
        if (self::ready()) {
            Database::insert('recruit_log', ['scope' => $scope, 'application_id' => $id, 'status' => $to, 'note' => $logNote !== '' ? mb_substr($logNote, 0, 600) : null, 'mailed' => $mailed === true ? 1 : 0, 'by_user' => $userId]);
        }
        return ['ok' => true, 'changed' => true, 'mailed' => $mailed];
    }

    /** @return list<array<string,mixed>> the history of an application, newest first */
    public static function history(string $scope, int $id): array
    {
        return self::ready() ? Database::all('SELECT l.*, u.name AS by_name FROM recruit_log l LEFT JOIN users u ON u.id = l.by_user WHERE l.scope = ? AND l.application_id = ? ORDER BY l.id DESC LIMIT 50', [$scope, $id]) : [];
    }

    /** The stage a status belongs to for the e-mails. */
    public static function stage(string $scope, string $status): ?string
    {
        if ($status === self::SCOPES[$scope]['accept']) {
            return 'accepted';
        }
        if ($status === self::SCOPES[$scope]['decline']) {
            return 'declined';
        }
        return match ($status) {
            'new' => 'received', 'reviewing', 'contacted', 'in_progress' => 'reviewing', 'shortlisted' => 'shortlisted', 'interview' => 'interview', 'on_hold' => 'on_hold', default => null,
        };
    }

    /** What the applicant is told, per stage: label (for the tracking page) and the e-mail paragraphs. @return array{label:string,subject:string,lines:list<string>}|null */
    public static function message(string $scope, array $row, string $stage): ?array
    {
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $what = match ($scope) {
            'ambassador' => 'your application to become an ambassador of ' . $site,
            'volunteer'  => 'your application to volunteer with ' . $site,
            default      => 'your application' . (trim((string) ($row['job_title'] ?? '')) !== '' ? ' for the position "' . $row['job_title'] . '"' : '') . ' at ' . $site,
        };
        $ref = (string) ($row['ref_no'] ?? '');
        return match ($stage) {
            'received' => ['label' => 'Received', 'subject' => 'We received your application' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['Thank you. We have received ' . $what . '.', $ref !== '' ? 'Your application number is ' . $ref . '. Keep it: you can check the progress of your application at any time with this number and your e-mail address.' : '', 'We will write to you at every stage.']],
            'reviewing' => ['label' => 'Under review', 'subject' => 'Your application is being reviewed' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['Our team has started to review ' . $what . '. We will tell you as soon as there is news.']],
            'shortlisted' => ['label' => 'Shortlisted', 'subject' => 'You have been shortlisted' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['Good news: ' . $what . ' has been shortlisted. We will contact you about the next step.']],
            'interview' => ['label' => 'Invited for interview', 'subject' => 'You are invited for an interview' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['Congratulations! After reviewing ' . $what . ', we would like to invite you for an interview.', 'Our team will contact you shortly with the date, the time and how to join. Please keep your phone and e-mail within reach.']],
            'accepted' => ['label' => $scope === 'job' ? 'Hired: onboarding' : 'Accepted: onboarding', 'subject' => 'Welcome aboard' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['We are delighted to tell you that ' . $what . ' has been accepted. Welcome to the team!', 'Our team will contact you with your onboarding steps: the paperwork, your briefing and your first tasks.']],
            'on_hold' => ['label' => 'On hold', 'subject' => 'An update on your application' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['We are keeping ' . $what . ' on hold for now and will come back to you.']],
            'declined' => ['label' => 'Not taken forward', 'subject' => 'Your application' . ($ref !== '' ? ' (' . $ref . ')' : ''),
                'lines' => ['Thank you for ' . $what . '. After careful review we are not able to take it forward at this time.', 'You are always welcome to support our work in other ways, for example by following our news, volunteering or donating.']],
            default => null,
        };
    }

    /** E-mail the applicant about a stage. @return bool|null null when this status has no message */
    public static function notify(string $scope, array $row, string $status, string $note = ''): ?bool
    {
        $stage = self::stage($scope, $status);
        $m = $stage !== null ? self::message($scope, $row, $stage) : null;
        if ($m === null || !Mailer::validEmail((string) ($row['email'] ?? ''))) {
            return null;
        }
        $site = trim((string) setting('site_name', 'Lumina Africa Foundation')) ?: 'Lumina Africa Foundation';
        $first = explode(' ', trim((string) $row['name']))[0] ?: 'there';
        $lines = array_values(array_filter($m['lines'], static fn(string $l): bool => $l !== ''));
        if (trim($note) !== '') {
            $lines[] = "A message from our team:\n" . trim($note);
        }
        $track = $scope === 'ambassador' && self::profileUrl($row) !== '' ? self::profileUrl($row) : abs_url('track-application');
        $trackLabel = $scope === 'ambassador' && self::profileUrl($row) !== '' ? 'Open my ambassador profile' : 'Track my application';
        $text = 'Hello ' . $first . ",\n\n" . implode("\n\n", $lines) . "\n\n" . $trackLabel . ': ' . $track . "\n\nWith thanks,\n" . $site . "\n";
        $html = '<!doctype html><html><body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#242A32"><div style="max-width:560px;margin:0 auto;background:#fff;border-radius:14px;padding:28px">'
            . '<p style="margin:0 0 6px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#A5521A;font-weight:700">' . htmlspecialchars($site, ENT_QUOTES) . '</p>'
            . ((string) ($row['ref_no'] ?? '') !== '' ? '<p style="margin:0 0 18px;font-size:13px;color:#5B6472">Application ' . htmlspecialchars((string) $row['ref_no'], ENT_QUOTES) . ' &middot; <strong>' . htmlspecialchars($m['label'], ENT_QUOTES) . '</strong></p>' : '')
            . '<p style="margin:0 0 14px;font-size:16px">Hello ' . htmlspecialchars($first, ENT_QUOTES) . ',</p>';
        foreach ($lines as $l) {
            $html .= '<p style="margin:0 0 16px;line-height:1.6;font-size:16px">' . nl2br(htmlspecialchars($l, ENT_QUOTES), false) . '</p>';
        }
        $html .= '<p style="margin:22px 0"><a href="' . htmlspecialchars($track, ENT_QUOTES) . '" style="background:#D9A441;color:#0B1B33;text-decoration:none;font-weight:700;padding:13px 24px;border-radius:999px;display:inline-block">' . htmlspecialchars($trackLabel, ENT_QUOTES) . '</a></p>'
            . '<p style="margin:18px 0 0;font-size:13px;color:#5B6472">With thanks, ' . htmlspecialchars($site, ENT_QUOTES) . '</p></div></body></html>';
        try {
            return Mailer::send((string) $row['email'], $m['subject'], $text, ['html' => $html]);
        } catch (\Throwable $e) {
            Log::warning('Recruitment e-mail failed: ' . $e->getMessage());
            return false;
        }
    }

    /** The confirmation with the tracking number, sent when the application is submitted. */
    public static function received(string $scope, int $id): ?bool
    {
        self::autoScore($scope, $id);
        $row = self::row($scope, $id);
        if ($row === null) {
            return null;
        }
        if (self::ready()) {
            Database::insert('recruit_log', ['scope' => $scope, 'application_id' => $id, 'status' => 'new', 'note' => 'Application submitted', 'mailed' => 1]);
        }
        return self::notify($scope, $row, 'new');
    }

    // ------------------------------------------------------------------------------------------------------------- public tracking

    /**
     * What an applicant may see: the stage of the application with this number and e-mail address (nothing else).
     * @return array{found:bool,ref:string,label:string,tone:string,updated:string,steps:list<array{label:string,state:string}>}
     */
    public static function track(string $ref, string $email): array
    {
        $ref = strtoupper(trim($ref));
        $email = strtolower(trim($email));
        $none = ['found' => false, 'ref' => $ref, 'label' => '', 'tone' => '', 'updated' => '', 'steps' => []];
        if (!self::ready() || preg_match('/^[A-Z]{3}-\d{4}-\d{3,}$/', $ref) !== 1 || $email === '') {
            return $none;
        }
        foreach (self::SCOPES as $scope => $cfg) {
            if ($cfg['prefix'] !== substr($ref, 0, 3)) {
                continue;
            }
            $row = Database::one("SELECT id, status, updated_at, email FROM `{$cfg['table']}` WHERE ref_no = ? AND LOWER(email) = ?", [$ref, $email]);
            if ($row === null) {
                return $none;
            }
            $stage = self::stage($scope, (string) $row['status']) ?? 'reviewing';
            $order = $scope === 'job' ? ['received', 'reviewing', 'shortlisted', 'interview', 'accepted'] : ['received', 'reviewing', 'interview', 'accepted'];
            $names = ['received' => 'Received', 'reviewing' => 'Under review', 'shortlisted' => 'Shortlisted', 'interview' => 'Interview', 'accepted' => $scope === 'job' ? 'Hired' : 'Accepted'];
            $pos = array_search($stage, $order, true);
            $steps = [];
            foreach ($order as $i => $s) {
                $steps[] = ['label' => $names[$s], 'state' => $pos === false ? 'todo' : ($i < $pos ? 'done' : ($i === $pos ? 'now' : 'todo'))];
            }
            $label = self::message($scope, ['name' => '', 'ref_no' => ''], $stage)['label'] ?? 'Under review';
            return ['found' => true, 'ref' => $ref, 'label' => $label, 'tone' => $stage === 'declined' ? 'bad' : ($stage === 'accepted' ? 'good' : 'wait'), 'updated' => (string) $row['updated_at'], 'steps' => $stage === 'declined' || $stage === 'on_hold' ? [] : $steps];
        }
        return $none;
    }
}
