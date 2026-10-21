<?php
declare(strict_types=1);

namespace App\Services\Academy;

use App\Core\Auth;
use App\Core\Database;
use App\Core\RateLimit;
use App\Core\Roles;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\User;

/**
 * VOLUNTEER FACILITATORS of the LAF Academy. Anyone can apply (no account needed) to facilitate one topic: they describe their experience, attach a CV (required)
 * and up to four supporting papers (certificates, degrees, letters). The files are stored privately and only administrators can open them. An administrator
 * approves (this creates the facilitator account), asks for more information, or declines; the applicant is told by e-mail. The role is voluntary and unpaid.
 */
final class FacilitatorApplications
{
    public const STATUSES = ['pending' => 'Waiting for review', 'more_info' => 'More information needed', 'approved' => 'Approved', 'rejected' => 'Not approved'];
    public const MAX_DOCS = 4;
    public const MAX_BYTES = 5 * 1024 * 1024;
    public const EXTS = ['pdf', 'doc', 'docx'];

    /** True when the applications table exists (the database update was imported). */
    public static function ready(): bool
    {
        static $ok = null;
        if ($ok === null) {
            try {
                $ok = Database::tableExists('academy_facilitator_applications');
            } catch (\Throwable) {
                $ok = false;
            }
        }
        return $ok;
    }

    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM academy_facilitator_applications WHERE id = ?', [$id]);
    }

    /** @return list<array{path:string,name:string,size:int}> */
    public static function docs(array $app): array
    {
        $d = json_decode((string) ($app['docs'] ?? ''), true);
        $out = [];
        foreach (is_array($d) ? $d : [] as $x) {
            if (is_array($x) && isset($x['path'], $x['name'])) {
                $out[] = ['path' => (string) $x['path'], 'name' => (string) $x['name'], 'size' => (int) ($x['size'] ?? 0)];
            }
        }
        return $out;
    }

    /** @return array<string,int> status => count, plus 'all' */
    public static function counts(): array
    {
        $out = ['all' => 0, 'pending' => 0, 'more_info' => 0, 'approved' => 0, 'rejected' => 0];
        foreach (Database::all('SELECT status, COUNT(*) n FROM academy_facilitator_applications GROUP BY status') as $r) {
            $out[(string) $r['status']] = (int) $r['n'];
            $out['all'] += (int) $r['n'];
        }
        return $out;
    }

    /**
     * Validate and store an application. $post = the posted fields, $cv / $papers = the uploaded files (PHP $_FILES style entries).
     * @param array<string,mixed> $post
     * @param list<array<string,mixed>> $papers
     * @return array{ok:bool,errors:array<string,string>,id?:int}
     */
    public static function submit(array $post, ?array $cv, array $papers, string $ip): array
    {
        $e = [];
        $name = trim(strip_tags((string) ($post['name'] ?? '')));
        $email = strtolower(trim((string) ($post['email'] ?? '')));
        $topic = (string) ($post['topic'] ?? '');
        $text = static fn(string $k, int $max): string => mb_substr(trim(strip_tags((string) ($post[$k] ?? ''))), 0, $max);
        if (mb_strlen($name) < 3) {
            $e['name'] = 'Please enter your full name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $e['email'] = 'Please enter a valid e-mail address.';
        }
        if (!isset(CourseService::CATEGORIES[$topic])) {
            $e['topic'] = 'Choose the topic you would like to facilitate.';
        }
        $detail = $text('topic_detail', 190);
        if ($detail === '') {
            $e['topic_detail'] = 'Tell us the particular subject, for example "Excel formulas" or "Introduction to Python".';
        }
        $experience = $text('experience', 3000);
        if (mb_strlen($experience) < 80) {
            $e['experience'] = 'Describe your experience and qualifications in a few sentences (at least 80 characters).';
        }
        $motivation = $text('motivation', 2000);
        if (mb_strlen($motivation) < 40) {
            $e['motivation'] = 'Tell us why you want to volunteer (at least 40 characters).';
        }
        $links = $text('links', 500);
        if ($links !== '' && preg_match('~^[^\s"<>]+(\s+[^\s"<>]+){0,4}$~', $links) !== 1) {
            $e['links'] = 'Enter up to five web addresses separated by spaces.';
        }
        if (empty($post['volunteer'])) {
            $e['volunteer'] = 'Please confirm that you understand this is a voluntary, unpaid role.';
        }
        if (empty($post['consent'])) {
            $e['consent'] = 'Please agree that we may review your documents for this application.';
        }
        if ($cv === null || (int) ($cv['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $e['cv'] = 'Please attach your CV (PDF or Word, up to 5 MB).';
        }
        if ($e === [] && Database::value("SELECT COUNT(*) FROM academy_facilitator_applications WHERE email = ? AND status IN ('pending','more_info')", [$email], 0) > 0) {
            $e['email'] = 'You already have an application waiting for review. We will write to you when it has been reviewed.';
        }
        if ($e === [] && !RateLimit::attempt('academy-facilitator-apply:' . sha1($ip), 3, 86400)) {
            $e['form'] = 'Too many applications from this connection today. Please try again tomorrow.';
        }
        $papers = array_values(array_filter($papers, static fn($f): bool => is_array($f) && (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE));
        if (count($papers) > self::MAX_DOCS) {
            $e['papers'] = 'Attach at most ' . self::MAX_DOCS . ' supporting papers.';
        }
        if ($e !== []) {
            return ['ok' => false, 'errors' => $e];
        }
        $stored = [];
        try {
            $cvUp = Uploader::privateFile($cv, 'academy', self::EXTS, self::MAX_BYTES);
            $stored[] = $cvUp['path'];
            $docs = [];
            foreach ($papers as $i => $f) {
                $up = Uploader::privateFile($f, 'academy', self::EXTS, self::MAX_BYTES);
                $stored[] = $up['path'];
                $docs[] = ['path' => $up['path'], 'name' => $up['name'], 'size' => $up['size']];
            }
        } catch (UploadException $x) {
            foreach ($stored as $p) {
                Uploader::deletePrivate($p);
            }
            return ['ok' => false, 'errors' => [($cvUp ?? null) === null ? 'cv' : 'papers' => $x->getMessage()]];
        }
        $id = Database::insert('academy_facilitator_applications', [
            'name' => mb_substr($name, 0, 120), 'email' => $email, 'phone' => $text('phone', 40) ?: null, 'country' => $text('country', 80) ?: null,
            'topic' => $topic, 'topic_detail' => $detail, 'headline' => $text('headline', 190) ?: null, 'experience' => $experience, 'motivation' => $motivation,
            'availability' => $text('availability', 190) ?: null, 'links' => $links ?: null, 'cv_path' => $cvUp['path'], 'cv_name' => $cvUp['name'],
            'docs' => $docs === [] ? null : json_encode($docs), 'consent_at' => date('Y-m-d H:i:s'), 'ip_hash' => sha1($ip . '|' . (string) setting('site_name', '')),
        ]);
        return ['ok' => true, 'errors' => [], 'id' => $id];
    }

    // ------------------------------------------------------------------------------------------------------------- decisions

    /** The staff accounts that may teach, by e-mail: role slugs that can be given to a new facilitator. */
    public static function facilitatorRole(): string
    {
        return in_array('facilitator', Roles::slugs(), true) ? 'facilitator' : 'editor';
    }

    /**
     * Approve: create (or link) the facilitator account. @return array{ok:bool,message:string,credentials?:array{name:string,email:string,password:string}}
     */
    public static function approve(array $app, int $reviewerId, string $applicantNote): array
    {
        if ($app['status'] === 'approved' && !empty($app['user_id'])) {
            return ['ok' => false, 'message' => 'This application is already approved.'];
        }
        $existing = Database::one('SELECT id, role, is_active FROM users WHERE email = ?', [(string) $app['email']]);
        $creds = null;
        if ($existing !== null) {
            if (!Roles::can((string) $existing['role'], 'academy.teach')) {
                return ['ok' => false, 'message' => 'A staff account with this e-mail address already exists but cannot teach in the Academy. Change its role under Users, then approve again.'];
            }
            $userId = (int) $existing['id'];
        } else {
            $password = Auth::generatePassword(16);
            $userId = User::create([
                'name' => (string) $app['name'], 'email' => (string) $app['email'], 'password_hash' => Auth::hashPassword($password),
                'role' => self::facilitatorRole(), 'is_active' => 1, 'must_change_password' => 1,
            ]);
            $creds = ['name' => (string) $app['name'], 'email' => (string) $app['email'], 'password' => $password];
        }
        Database::update('academy_facilitator_applications', [
            'status' => 'approved', 'applicant_note' => mb_substr($applicantNote, 0, 500) ?: null, 'reviewed_by' => $reviewerId, 'reviewed_at' => date('Y-m-d H:i:s'), 'user_id' => $userId,
        ], 'id = ?', [(int) $app['id']]);
        return ['ok' => true, 'message' => $creds === null ? 'Approved. This person already has a teaching account.' : 'Approved and the facilitator account was created.'] + ($creds !== null ? ['credentials' => $creds] : []);
    }

    public static function setStatus(array $app, string $status, int $reviewerId, string $applicantNote): void
    {
        Database::update('academy_facilitator_applications', [
            'status' => $status, 'applicant_note' => mb_substr($applicantNote, 0, 500) ?: null, 'reviewed_by' => $reviewerId, 'reviewed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $app['id']]);
    }

    /** Delete the application and its files. */
    public static function delete(array $app): void
    {
        Uploader::deletePrivate((string) ($app['cv_path'] ?? ''));
        foreach (self::docs($app) as $d) {
            Uploader::deletePrivate($d['path']);
        }
        Database::execute('DELETE FROM academy_facilitator_applications WHERE id = ?', [(int) $app['id']]);
    }

    /** The approved topic of a staff member (from the application that created or linked their account), or null. */
    public static function topicOfUser(int $userId): ?string
    {
        $t = Database::value("SELECT topic FROM academy_facilitator_applications WHERE user_id = ? AND status = 'approved' ORDER BY id DESC LIMIT 1", [$userId]);
        return is_string($t) && $t !== '' ? $t : null;
    }
}
