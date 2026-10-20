<?php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\ImageOptimizer;
use App\Core\Log;
use App\Core\PdfValidator;
use App\Core\RateLimit;
use App\Core\Response;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\Member;
use App\Services\Membership\Card\CardData;
use App\Services\Membership\Card\PdfWriter;

/**
 * The PRIVATE FILES of a member: the headshot (printed on the membership card) and the proof of payment. Contract: docs/MEMBERSHIP_CARD.md.
 * OWNER: architect (frozen). Callers: the registration form, the member's private dashboard page, the admin member screens.
 *
 * Storage: below storage/private/members/ (outside the public web root, never served directly):
 *   photos/<32 random hex>.jpg|png      proofs/<32 random hex>.jpg|png|webp|pdf
 * Names are random, nothing in them identifies a person. Files are served ONLY through stream() / streamPayment() by a controller that has already
 * authorised the caller (the holder of the dashboard token for their own files, or staff with members.manage). The database keeps the relative path.
 *
 * Validation (before anything is stored): real image type by magic bytes + finfo + getimagesize (JPEG / PNG / WebP; GIF, SVG, HTML and polyglots
 * refused), 5 MB, headshot at least 300 x 300 px, proof images re-encoded without metadata, proof PDFs through PdfValidator in STRICT mode (no scripts,
 * no remote actions, not encrypted). The headshot is EXIF-oriented, flattened, cropped to a square with a face-friendly top bias (ImageOptimizer::portrait)
 * and stored as a metadata-free JPEG. Without GD the validated JPEG / PNG original is kept (EXIF removed by ImageScrubber), `photo_original` = 1 tells staff
 * it could not be cropped.
 *
 * Every result array has 'ok', 'code' (machine readable: none, size, type, small, invalid, failed, consent, closed, rate, missing), 'message' (the editable copy of
 * Pages > Membership > "uploads", safe to show to the person) and the updated 'member' row. Audit entries carry ids and references only, never names or file names.
 */
final class MemberFiles
{
    public const PHOTO_MAX_BYTES = 5 * 1024 * 1024;
    public const PROOF_MAX_BYTES = 5 * 1024 * 1024;
    public const PHOTO_MIN_SIDE = 300;
    public const PHOTO_DIR = 'members/photos';
    public const PROOF_DIR = 'members/proofs';
    /** Proof statuses. */
    public const PROOF_STATUSES = ['none', 'received', 'verified', 'rejected'];
    /** Statuses in which the applicant may still upload (photo until the card is issued, see canUpload()). */
    public const UPLOAD_STATUSES = ['pending', 'approved'];

    // ------------------------------------------------------------------------------------------------ rules and copy

    /** Setting membership_photo_required: must the registration form include a photo? (default yes) */
    public static function photoRequired(): bool
    {
        return \App\Services\Membership\Card\CardSettings::bool('membership_photo_required');
    }

    /** Can the applicant upload / replace now? Photo: while pending, or approved with no card issued yet (a replacement after issue is allowed and only FLAGS a re-issue). Proof: pending or approved and not fully verified. */
    public static function canUpload(array $member, string $kind): bool
    {
        if (!empty($member['anonymised_at']) || !in_array((string) $member['status'], self::UPLOAD_STATUSES, true)) {
            return false;
        }
        if ($kind === 'photo') {
            return true;
        }
        return (string) ($member['proof_status'] ?? 'none') !== 'verified';
    }

    /** Editable wording with a built-in fallback (registry section "uploads"). */
    public static function msg(string $key, string $fallback, array $vars = []): string
    {
        $t = MembershipService::copy('uploads.' . $key, $fallback);
        foreach ($vars as $k => $v) {
            $t = str_replace('{' . $k . '}', (string) $v, $t);
        }
        return $t;
    }

    private const DEFAULT_MESSAGES = [
        'photo_none'    => 'Please choose a photo.',
        'photo_size'    => 'That photo is too large. Please use one that is 5 MB or smaller.',
        'photo_type'    => 'Please choose a JPG, PNG or WebP picture.',
        'photo_small'   => 'That photo is too small. Please use one that is at least {min} x {min} pixels.',
        'photo_invalid' => 'We could not read that picture. Please choose another photo.',
        'photo_dimensions' => 'That photo is too big for this website to process. Please choose a smaller copy: most phones can share a photo at "Medium" or "Small" size, and 4000 pixels on the longest side is plenty.',
        'photo_unsupported' => 'That kind of picture cannot be printed on the card. Please choose a normal JPG photo.',
        'photo_failed'  => 'The photo could not be uploaded. Please try again.',
        'photo_consent' => 'Please tick the box to agree that your photo is printed on your membership card.',
        'photo_closed'  => 'The photo can no longer be changed here. Please contact us.',
        'photo_missing' => 'Please add your photo.',
        'proof_none'    => 'Please choose a file.',
        'proof_size'    => 'That file is too large. Please use one that is 5 MB or smaller.',
        'proof_type'    => 'Please choose a JPG, PNG, WebP or PDF file.',
        'proof_invalid' => 'We could not read that file. Please choose another one.',
        'proof_dimensions' => 'That picture is too big for this website to process. Please choose a smaller copy (4000 pixels on the longest side is plenty) or a PDF.',
        'proof_failed'  =>'The file could not be uploaded. Please try again.',
        'proof_closed'  => 'Proof of payment can no longer be uploaded here. Please contact us.',
        'rate'          => 'You have uploaded a lot of files. Please wait a little and try again.',
    ];

    /** The machine code for a picture refusal, so the person is told the REAL reason (too small, too big for this server, wrong type) and not "we could not read it". */
    private static function codeFor(UploadException $e): string
    {
        $t = $e->getMessage();
        return match (true) {
            str_contains($t, 'too small') => 'small',
            str_contains($t, 'WebP') => 'type',
            str_contains($t, 'too large in pixels'), str_contains($t, 'too large for this server') => 'dimensions',
            default => 'invalid',
        };
    }

    private static function fail(string $kind, string $code, ?array $member = null, ?string $notice = null): array
    {
        $key = $code === 'rate' ? 'rate' : $kind . '_' . $code;
        $text = 'err_' . $key;
        return ['ok' => false, 'code' => $code, 'message' => self::msg($text, self::DEFAULT_MESSAGES[$key] ?? self::DEFAULT_MESSAGES[$kind . '_failed'] ?? 'The file could not be uploaded.', ['min' => self::PHOTO_MIN_SIDE]),
            'member' => $member, 'notice' => $notice];
    }

    // ------------------------------------------------------------------------------------------------ throttling

    /**
     * Count an upload attempt for a member and an address; false = too many (a bad token holder cannot be told apart from a good one, so the limits are generous
     * for a person and tight for a script): 8 per hour per member, 30 per hour per address.
     */
    public static function allowUpload(int $memberId, string $ip): bool
    {
        return RateLimit::attempt('member-upload:m' . $memberId, 8, 3600) && RateLimit::attempt('member-upload-ip:' . $ip, 30, 3600);
    }

    // ------------------------------------------------------------------------------------------------ cheap validation (nothing stored)

    /**
     * Check an uploaded file WITHOUT storing it: type, size and, for a headshot, the minimum size. Used by the registration form before the application is created.
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int}|null $file
     * @return array{ok:bool,empty:bool,code:?string,message:?string}  empty = no file chosen (not an error)
     */
    public static function inspectUpload(?array $file, string $kind): array
    {
        if ($file === null || !isset($file['tmp_name'], $file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'empty' => true, 'code' => null, 'message' => null];
        }
        $bad = static function (string $code) use ($kind): array {
            $f = self::fail($kind, $code);
            return ['ok' => false, 'empty' => false, 'code' => $code, 'message' => $f['message']];
        };
        $err = (int) $file['error'];
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return $bad('size');
        }
        if ($err !== UPLOAD_ERR_OK) {
            return $bad('failed');
        }
        $tmp = (string) $file['tmp_name'];
        if (!is_file($tmp) || (PHP_SAPI !== 'cli' && !is_uploaded_file($tmp))) {
            return $bad('failed');
        }
        $size = (int) filesize($tmp);
        $max = $kind === 'photo' ? self::PHOTO_MAX_BYTES : self::PROOF_MAX_BYTES;
        if ($size <= 0) {
            return $bad('invalid');
        }
        if ($size > $max) {
            return $bad('size');
        }
        $head = (string) file_get_contents($tmp, false, null, 0, 16);
        $isImage = str_starts_with($head, "\xFF\xD8\xFF") || str_starts_with($head, "\x89PNG\r\n\x1a\n") || (str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP');
        $isPdf = $kind === 'proof' && str_starts_with($head, '%PDF-');
        if (!$isImage && !$isPdf) {
            return $bad('type');
        }
        try {
            if ($isPdf) {
                PdfValidator::inspect($tmp, self::PROOF_MAX_BYTES, true);
            } else {
                $info = ImageOptimizer::inspect($tmp, ['noun' => 'picture', 'max_bytes' => $max]);
                if ($kind === 'photo' && min($info['width'], $info['height']) < self::PHOTO_MIN_SIDE) {
                    return $bad('small');
                }
                $caps = ImageOptimizer::capabilities();
                if ($kind === 'photo' && $caps['gd'] === false && $info['mime'] === 'image/webp') {
                    return $bad('type');                  // the card PDF cannot embed WebP and this server cannot convert it
                }
                if ($kind === 'photo' && $caps['gd'] === false && $info['mime'] === 'image/png' && PdfWriter::imageInfo((string) file_get_contents($tmp)) === null) {
                    return $bad('unsupported');           // no GD: the card writer embeds only plain PNGs (no interlacing, 16 bit, or a large picture with transparency)
                }
                // with GD the picture is decoded in memory: refuse now, BEFORE the application is created, what portrait() would refuse later for lack of memory
                if ($kind === 'photo' && $caps['gd'] === true && !ImageOptimizer::fitsInMemory($info['width'], $info['height'], 3.2)) {
                    return $bad('dimensions');
                }
            }
        } catch (UploadException $e) {
            return $bad(self::codeFor($e));
        }
        return ['ok' => true, 'empty' => false, 'code' => null, 'message' => null];
    }

    /**
     * Validate the file part of the REGISTRATION FORM (nothing is stored). $files = ['photo' => $request->file('photo'), 'proof' => $request->file('proof')];
     * $input = the posted fields (needs 'photo_consent'). Returns field => message for the error summary (keys: photo, photo_consent, proof); empty = fine.
     * Rules: the photo is required when setting membership_photo_required is on; when a photo is given the consent box must be ticked; the proof is optional.
     * @param array<string,?array> $files @param array<string,mixed> $input @return array<string,string>
     */
    public static function checkApplication(array $files, array $input): array
    {
        $errors = [];
        $photo = self::inspectUpload($files['photo'] ?? null, 'photo');
        if (!$photo['ok']) {
            $errors['photo'] = (string) $photo['message'];
        } elseif ($photo['empty'] && self::photoRequired()) {
            $errors['photo'] = self::msg('err_photo_missing', self::DEFAULT_MESSAGES['photo_missing']);
        }
        if (!$photo['empty'] && $photo['ok'] && !self::ticked($input['photo_consent'] ?? null)) {
            $errors['photo_consent'] = self::msg('err_photo_consent', self::DEFAULT_MESSAGES['photo_consent']);
        }
        $proof = self::inspectUpload($files['proof'] ?? null, 'proof');
        if (!$proof['ok']) {
            $errors['proof'] = (string) $proof['message'];
        }
        return $errors;
    }

    /**
     * Store the files of a just-created application (after checkApplication() passed). A failure here never undoes the application: the result says what
     * could not be saved so the page can tell the applicant to add it from their private page.
     * @param array<string,?array> $files @param array<string,mixed> $input
     * @return array{photo:?array,proof:?array}
     */
    public static function storeApplication(int $memberId, array $files, array $input): array
    {
        $out = ['photo' => null, 'proof' => null];
        try {
            if (($files['photo'] ?? null) !== null && !self::inspectUpload($files['photo'], 'photo')['empty']) {
                $out['photo'] = self::storePhoto($memberId, $files['photo'], ['consent' => self::ticked($input['photo_consent'] ?? null), 'source' => 'apply', 'skip_rate' => true]);
            }
            if (($files['proof'] ?? null) !== null && !self::inspectUpload($files['proof'], 'proof')['empty']) {
                $out['proof'] = self::storeProof($memberId, $files['proof'], ['source' => 'apply', 'skip_rate' => true]);
            }
        } catch (\Throwable $e) {
            Log::error('Member file storage failed', ['member' => $memberId, 'error' => $e::class]);
        }
        return $out;
    }

    // ------------------------------------------------------------------------------------------------ photo

    /**
     * Store or replace the headshot. $opts: consent (bool, required unless by_staff), by_staff (bool), user_id, source (apply | dashboard | staff), ip (for the rate limit),
     * skip_rate (the caller already limited), notify (false = do not run the card triggers).
     * A replacement after the card was issued does NOT change the card: it sets card_needs_reissue (reason "photo") for staff.
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int}|null $file
     * @return array{ok:bool,code:?string,message:string,member:?array,notice:?string}
     */
    public static function storePhoto(int $memberId, ?array $file, array $opts = []): array
    {
        $m = Member::find($memberId);
        if ($m === null || !empty($m['anonymised_at'])) {
            return self::fail('photo', 'failed');
        }
        $byStaff = !empty($opts['by_staff']);
        if (!$byStaff && !self::canUpload($m, 'photo')) {
            return self::fail('photo', 'closed', $m);
        }
        if (empty($opts['skip_rate']) && !$byStaff && !self::allowUpload($memberId, (string) ($opts['ip'] ?? ''))) {
            return self::fail('photo', 'rate', $m);
        }
        if (!$byStaff && !self::ticked($opts['consent'] ?? null)) {
            return self::fail('photo', 'consent', $m);
        }
        $chk = self::inspectUpload($file, 'photo');
        if ($chk['empty']) {
            return self::fail('photo', 'none', $m);
        }
        if (!$chk['ok']) {
            return self::fail('photo', (string) $chk['code'], $m);
        }
        $rel = self::PHOTO_DIR . '/' . bin2hex(random_bytes(16)) . '.jpg';
        $abs = STORAGE_PATH . '/private/' . $rel;
        try {
            $res = ImageOptimizer::portrait((string) $file['tmp_name'], $abs, ['min_side' => self::PHOTO_MIN_SIDE, 'max_bytes' => self::PHOTO_MAX_BYTES]);
        } catch (UploadException $e) {
            return self::fail('photo', self::codeFor($e), $m);
        }
        if ($res['ext'] !== 'jpg') {                              // no GD: the original was kept under its own type
            $rel = self::PHOTO_DIR . '/' . bin2hex(random_bytes(16)) . '.' . $res['ext'];
            if (!@rename($abs, STORAGE_PATH . '/private/' . $rel)) {
                @unlink($abs);
                return self::fail('photo', 'failed', $m);
            }
        }
        $old = Database::transaction(function () use ($memberId, $rel, $res, $byStaff, $opts): ?string {
            $row = Database::one('SELECT photo_path, card_status FROM members WHERE id = ? FOR UPDATE', [$memberId]);
            $set = ['photo_path' => $rel, 'photo_updated_at' => date('Y-m-d H:i:s'), 'photo_original' => $res['optimised'] ? 0 : 1];
            if (self::ticked($opts['consent'] ?? null)) {
                $set['photo_consent_at'] = date('Y-m-d H:i:s');
            }
            if ($row !== null && $row['card_status'] === 'issued') {
                $set['card_needs_reissue'] = 1;
                $set['card_reissue_reason'] = $byStaff ? 'photo_staff' : 'photo';
            }
            Database::update('members', $set, 'id = ?', [$memberId]);
            return $row['photo_path'] ?? null;
        });
        if ($old !== null && $old !== '') {
            Uploader::deletePrivate($old);
        }
        AuditLog::record('member.photo', 'member', $memberId, 'Photo ' . ($old ? 'replaced' : 'added') . ' on ' . $m['application_ref'],
            ['ref' => $m['application_ref'], 'by_staff' => $byStaff, 'source' => (string) ($opts['source'] ?? ''), 'original' => !$res['optimised']]);
        MembershipService::flush();
        $fresh = Member::find($memberId);
        if (($opts['notify'] ?? true) !== false && $fresh !== null) {
            CardService::event($memberId, 'photo', ['user_id' => $opts['user_id'] ?? null]);
            $fresh = Member::find($memberId);
        }
        return ['ok' => true, 'code' => null, 'message' => self::msg('photo_saved', 'Your photo was saved.'), 'member' => $fresh, 'notice' => $res['notice']];
    }

    /** Staff: remove the photo (the card then shows the initials). Flags a re-issue when a card exists. @return array{ok:bool,message:string,member:?array} */
    public static function removePhoto(int $memberId, ?int $userId = null): array
    {
        $m = Member::find($memberId);
        if ($m === null || empty($m['photo_path'])) {
            return ['ok' => false, 'message' => 'There is no photo to remove.', 'member' => $m];
        }
        $old = (string) $m['photo_path'];
        $set = ['photo_path' => null, 'photo_updated_at' => date('Y-m-d H:i:s'), 'photo_original' => 0, 'photo_consent_at' => null];
        if ($m['card_status'] === 'issued') {
            $set['card_needs_reissue'] = 1;
            $set['card_reissue_reason'] = 'photo_staff';
        }
        Database::update('members', $set, 'id = ?', [$memberId]);
        Uploader::deletePrivate($old);
        AuditLog::record('member.photo_remove', 'member', $memberId, 'Photo removed from ' . $m['application_ref'], ['ref' => $m['application_ref'], 'user_id' => $userId]);
        return ['ok' => true, 'message' => 'The photo was removed.', 'member' => Member::find($memberId)];
    }

    // ------------------------------------------------------------------------------------------------ proof of payment

    /**
     * Store or replace the member's proof of payment (status becomes "received": waiting for staff). $opts: user_id, source, ip, skip_rate, by_staff.
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int}|null $file
     * @return array{ok:bool,code:?string,message:string,member:?array,notice:?string}
     */
    public static function storeProof(int $memberId, ?array $file, array $opts = []): array
    {
        $m = Member::find($memberId);
        if ($m === null || !empty($m['anonymised_at'])) {
            return self::fail('proof', 'failed');
        }
        $byStaff = !empty($opts['by_staff']);
        if (!$byStaff && !self::canUpload($m, 'proof')) {
            return self::fail('proof', 'closed', $m);
        }
        if (empty($opts['skip_rate']) && !$byStaff && !self::allowUpload($memberId, (string) ($opts['ip'] ?? ''))) {
            return self::fail('proof', 'rate', $m);
        }
        $chk = self::inspectUpload($file, 'proof');
        if ($chk['empty']) {
            return self::fail('proof', 'none', $m);
        }
        if (!$chk['ok']) {
            return self::fail('proof', (string) $chk['code'], $m);
        }
        $stored = self::storeProofFile((string) $file['tmp_name'], (string) ($file['name'] ?? ''));
        if ($stored === null) {
            return self::fail('proof', 'invalid', $m);
        }
        $old = Database::transaction(function () use ($memberId, $stored): ?string {
            $row = Database::one('SELECT proof_path FROM members WHERE id = ? FOR UPDATE', [$memberId]);
            Database::update('members', [
                'proof_path' => $stored['path'], 'proof_name' => $stored['name'], 'proof_mime' => $stored['mime'], 'proof_uploaded_at' => date('Y-m-d H:i:s'),
                'proof_status' => 'received', 'proof_verified_by' => null, 'proof_verified_at' => null,
            ], 'id = ?', [$memberId]);
            return $row['proof_path'] ?? null;
        });
        if ($old !== null && $old !== '') {
            Uploader::deletePrivate($old);
        }
        AuditLog::record('member.proof', 'member', $memberId, 'Proof of payment ' . ($old ? 'replaced' : 'uploaded') . ' on ' . $m['application_ref'],
            ['ref' => $m['application_ref'], 'by_staff' => $byStaff, 'source' => (string) ($opts['source'] ?? ''), 'mime' => $stored['mime']]);
        MembershipService::flush();
        return ['ok' => true, 'code' => null, 'message' => self::msg('proof_saved', 'Thank you. We received your proof of payment.'), 'member' => Member::find($memberId), 'notice' => $stored['notice']];
    }

    /**
     * Validate and store one proof file below storage/private/members/proofs. Images are re-encoded without metadata; PDFs are kept (strictly validated).
     * @return array{path:string,name:string,mime:string,notice:?string}|null null = the file is not acceptable
     */
    private static function storeProofFile(string $tmp, string $clientName): ?array
    {
        $head = (string) file_get_contents($tmp, false, null, 0, 8);
        $name = self::displayName($clientName);
        try {
            if (str_starts_with($head, '%PDF-')) {
                PdfValidator::inspect($tmp, self::PROOF_MAX_BYTES, true);
                $rel = self::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.pdf';
                self::ensureDir(STORAGE_PATH . '/private/' . self::PROOF_DIR);
                if (!@copy($tmp, STORAGE_PATH . '/private/' . $rel)) {
                    return null;
                }
                @chmod(STORAGE_PATH . '/private/' . $rel, 0640);
                return ['path' => $rel, 'name' => $name !== '' ? $name : 'proof.pdf', 'mime' => 'application/pdf', 'notice' => null];
            }
            $tmpRel = self::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.jpg';
            $res = ImageOptimizer::evidence($tmp, STORAGE_PATH . '/private/' . $tmpRel, ['max_bytes' => self::PROOF_MAX_BYTES]);
            $rel = $tmpRel;
            if ($res['ext'] !== 'jpg') {
                $rel = self::PROOF_DIR . '/' . bin2hex(random_bytes(16)) . '.' . $res['ext'];
                if (!@rename(STORAGE_PATH . '/private/' . $tmpRel, STORAGE_PATH . '/private/' . $rel)) {
                    @unlink(STORAGE_PATH . '/private/' . $tmpRel);
                    return null;
                }
            }
            return ['path' => $rel, 'name' => $name !== '' ? $name : 'proof.' . $res['ext'], 'mime' => $res['mime'], 'notice' => $res['notice']];
        } catch (UploadException) {
            return null;
        }
    }

    /** A harmless display name for a client file name: letters, digits and a few marks, no path, at most 60 characters. */
    public static function displayName(string $client): string
    {
        $base = basename(str_replace(chr(92), '/', $client));
        $base = trim((string) preg_replace('/[^\p{L}\p{N}._ ()\-]+/u', '_', $base));
        return mb_substr($base, 0, 60);
    }

    /**
     * Staff: set the status of the proof. verified = the payment was checked; rejected = "needs a better copy" (the applicant is e-mailed a polite request with
     * their dashboard link, at most once per 6 hours per member); received = back to waiting.
     * @return array{ok:bool,message:string,member:?array,mailed:bool}
     */
    public static function setProofStatus(int $memberId, string $status, ?int $userId = null): array
    {
        $m = Member::find($memberId);
        if ($m === null || !in_array($status, ['verified', 'rejected', 'received'], true)) {
            return ['ok' => false, 'message' => 'Member not found.', 'member' => $m, 'mailed' => false];
        }
        if (empty($m['proof_path'])) {
            return ['ok' => false, 'message' => 'No proof of payment has been uploaded.', 'member' => $m, 'mailed' => false];
        }
        Database::update('members', [
            'proof_status' => $status,
            'proof_verified_by' => $status === 'received' ? null : $userId,
            'proof_verified_at' => $status === 'received' ? null : date('Y-m-d H:i:s'),
        ], 'id = ?', [$memberId]);
        AuditLog::record('member.proof_status', 'member', $memberId, 'Proof of payment of ' . $m['application_ref'] . ' marked ' . $status, ['ref' => $m['application_ref'], 'status' => $status, 'user_id' => $userId]);
        MembershipService::flush();
        $mailed = false;
        if ($status === 'rejected' && RateLimit::attempt('member-proof-mail:' . $memberId, 1, 21600)) {
            $mailed = MembershipMail::proofRejected(Member::find($memberId) ?? $m);
        }
        $msg = match ($status) {
            'verified' => 'The proof of payment was marked as verified.',
            'rejected' => $mailed ? 'The applicant was asked for a clearer copy.' : 'The proof was marked as needing a better copy (an e-mail was sent less than 6 hours ago, so none was sent now).',
            default => 'The proof of payment is waiting for a check again.',
        };
        return ['ok' => true, 'message' => $msg, 'member' => Member::find($memberId), 'mailed' => $mailed];
    }

    /** Staff: remove the uploaded proof. @return array{ok:bool,message:string} */
    public static function removeProof(int $memberId, ?int $userId = null): array
    {
        $m = Member::find($memberId);
        if ($m === null || empty($m['proof_path'])) {
            return ['ok' => false, 'message' => 'There is no proof to remove.'];
        }
        $old = (string) $m['proof_path'];
        Database::update('members', ['proof_path' => null, 'proof_name' => null, 'proof_mime' => null, 'proof_uploaded_at' => null, 'proof_status' => 'none',
            'proof_verified_by' => null, 'proof_verified_at' => null], 'id = ?', [$memberId]);
        Uploader::deletePrivate($old);
        AuditLog::record('member.proof_remove', 'member', $memberId, 'Proof of payment removed from ' . $m['application_ref'], ['ref' => $m['application_ref'], 'user_id' => $userId]);
        MembershipService::flush();
        return ['ok' => true, 'message' => 'The proof of payment was removed.'];
    }

    /**
     * Staff "record payment WITH the proof attached": give a payment row a private COPY of the member's proof (so deleting one never affects the other),
     * or, with $file, a newly uploaded proof for the payment. Returns false when there is nothing to attach or the file is not acceptable.
     * @param array{tmp_name?:string,name?:string,error?:int,size?:int}|null $file
     */
    public static function attachProofToPayment(int $paymentId, int $memberId, ?array $file = null): bool
    {
        $payment = Database::one('SELECT id, member_id, proof_path FROM member_payments WHERE id = ?', [$paymentId]);
        if ($payment === null || (int) $payment['member_id'] !== $memberId) {
            return false;
        }
        if ($file !== null && !self::inspectUpload($file, 'proof')['empty']) {
            if (!self::inspectUpload($file, 'proof')['ok']) {
                return false;
            }
            $stored = self::storeProofFile((string) $file['tmp_name'], (string) ($file['name'] ?? ''));
        } else {
            $m = Member::find($memberId);
            if ($m === null || empty($m['proof_path'])) {
                return false;
            }
            $copy = Uploader::copyPrivate((string) $m['proof_path']);
            $stored = $copy === null ? null : ['path' => $copy, 'name' => (string) ($m['proof_name'] ?? 'proof'), 'mime' => (string) ($m['proof_mime'] ?? 'application/octet-stream')];
        }
        if ($stored === null) {
            return false;
        }
        if (!empty($payment['proof_path'])) {
            Uploader::deletePrivate((string) $payment['proof_path']);
        }
        Database::update('member_payments', ['proof_path' => $stored['path'], 'proof_name' => $stored['name'], 'proof_mime' => $stored['mime']], 'id = ?', [$paymentId]);
        return true;
    }

    // ------------------------------------------------------------------------------------------------ reading and streaming

    /** The headshot for the card: ['bytes' => ..., 'mime' => 'image/jpeg'|'image/png'|'image/webp'] or null. */
    public static function photoForCard(array $member): ?array
    {
        $rel = (string) ($member['photo_path'] ?? '');
        if ($rel === '') {
            return null;
        }
        try {
            $abs = Uploader::privatePath($rel);
        } catch (HttpException) {
            return null;
        }
        $bytes = @file_get_contents($abs);
        if ($bytes === false || $bytes === '') {
            return null;
        }
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        return ['bytes' => $bytes, 'mime' => ['png' => 'image/png', 'webp' => 'image/webp'][$ext] ?? 'image/jpeg'];
    }

    public static function hasPhoto(array $member): bool
    {
        return !empty($member['photo_path']) && Uploader::privateExists((string) $member['photo_path']);
    }

    public static function hasProof(array $member): bool
    {
        return !empty($member['proof_path']) && Uploader::privateExists((string) $member['proof_path']);
    }

    /**
     * Stream the member's own file. The CALLER has already authorised the person (dashboard token holder for their own record, or staff with members.manage).
     * kind = photo | proof. Headers: private, no-store, nosniff, noindex, no referrer; images are shown inline, PDFs are always downloaded.
     * Throws HttpException(404) when there is no file.
     */
    public static function stream(array $member, string $kind, bool $download = false): Response
    {
        $rel = (string) ($kind === 'photo' ? ($member['photo_path'] ?? '') : ($member['proof_path'] ?? ''));
        if ($rel === '') {
            throw new HttpException(404);
        }
        $mime = $kind === 'photo' ? self::mimeOfExt($rel) : (string) ($member['proof_mime'] ?? self::mimeOfExt($rel));
        $name = $kind === 'photo' ? 'photo.' . pathinfo($rel, PATHINFO_EXTENSION) : (string) ($member['proof_name'] ?? 'proof.' . pathinfo($rel, PATHINFO_EXTENSION));
        return self::send($rel, $mime, $name, $download);
    }

    /** Stream the proof attached to a payment row (staff). */
    public static function streamPayment(array $payment, bool $download = false): Response
    {
        $rel = (string) ($payment['proof_path'] ?? '');
        if ($rel === '') {
            throw new HttpException(404);
        }
        return self::send($rel, (string) ($payment['proof_mime'] ?? self::mimeOfExt($rel)), (string) ($payment['proof_name'] ?? basename($rel)), $download);
    }

    private static function send(string $rel, string $mime, string $name, bool $download): Response
    {
        $abs = Uploader::privatePath($rel);                          // 404 for anything outside storage/private
        $isPdf = $mime === 'application/pdf';
        $r = Response::download($abs, $name, $mime, !$download && !$isPdf);
        return $r->header('Cache-Control', 'private, no-store, max-age=0')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Content-Security-Policy', "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox");
    }

    private static function mimeOfExt(string $path): string
    {
        return ['png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'image/jpeg';
    }

    // ------------------------------------------------------------------------------------------------ deletion and retention

    /**
     * Every private file path of a member: photo, proof, the card's own copy of the photo, and the proofs attached to their payments.
     * @return list<string>
     */
    public static function collectPaths(array $member): array
    {
        $paths = [];
        foreach (['photo_path', 'proof_path', 'card_photo_path'] as $k) {
            if (!empty($member[$k])) {
                $paths[] = (string) $member[$k];
            }
        }
        if (!empty($member['id'])) {
            foreach (Database::all('SELECT proof_path FROM member_payments WHERE member_id = ? AND proof_path IS NOT NULL', [(int) $member['id']]) as $p) {
                $paths[] = (string) $p['proof_path'];
            }
        }
        return array_values(array_unique($paths));
    }

    /** Delete the given private files (paths from collectPaths()); returns how many were removed. @param list<string> $paths */
    public static function deletePaths(array $paths): int
    {
        $n = 0;
        foreach ($paths as $rel) {
            if (str_starts_with($rel, 'members/') && Uploader::deletePrivate($rel)) {
                $n++;
            }
        }
        return $n;
    }

    /** Delete all files of a member and clear the columns that point at them (used before a member row is deleted, and by erasure). Returns the number of files removed. */
    public static function deleteAll(array $member): int
    {
        $paths = self::collectPaths($member);
        if (!empty($member['id'])) {
            // the card's photo copy and snapshot (name, place) go with the photo: the card is revoked and the private page link and QR code stop working
            Database::update('members', ['photo_path' => null, 'photo_consent_at' => null, 'proof_path' => null, 'proof_name' => null, 'proof_mime' => null, 'proof_status' => 'none',
                'proof_uploaded_at' => null, 'card_photo_path' => null, 'card_snapshot' => null, 'dashboard_token' => null, 'card_verify_token' => null,
                'card_needs_reissue' => 0, 'card_reissue_reason' => null,
                'card_status' => (string) ($member['card_status'] ?? 'none') === 'none' ? 'none' : 'revoked'], 'id = ?', [(int) $member['id']]);
            Database::execute('UPDATE member_payments SET proof_path = NULL, proof_name = NULL, proof_mime = NULL WHERE member_id = ?', [(int) $member['id']]);
        }
        return self::deletePaths($paths);
    }

    /**
     * Retention: delete the photo and proof of members who were rejected or cancelled more than $days ago (default: setting membership_proof_retention_days, 0 = never).
     * Run it from bin/membership-maintenance.php (cron, daily). Audit: counts only.
     * @return array{members:int,files:int}
     */
    public static function purgeStale(?int $days = null): array
    {
        $days ??= \App\Services\Membership\Card\CardSettings::int('membership_proof_retention_days');
        if ($days <= 0) {
            return ['members' => 0, 'files' => 0];
        }
        $cut = date('Y-m-d H:i:s', time() - $days * 86400);
        $rows = Database::all(
            "SELECT * FROM members WHERE status IN ('rejected','cancelled') AND status_changed_at IS NOT NULL AND status_changed_at < ? AND (photo_path IS NOT NULL OR proof_path IS NOT NULL OR card_photo_path IS NOT NULL OR card_snapshot IS NOT NULL OR dashboard_token IS NOT NULL OR card_verify_token IS NOT NULL) AND anonymised_at IS NULL LIMIT 500",
            [$cut]
        );
        $files = 0;
        foreach ($rows as $m) {
            $files += self::deleteAll($m);
        }
        if ($rows !== []) {
            AuditLog::record('member.files_purged', 'member', null, 'Deleted the photos and proofs of ' . count($rows) . ' rejected or cancelled member(s)', ['members' => count($rows), 'files' => $files, 'days' => $days]);
        }
        return ['members' => count($rows), 'files' => $files];
    }

    /**
     * Delete files in storage/private/members/ that no row points to and that are older than one day (left behind when a request died between writing the file and
     * saving the row). Returns how many were deleted. Safe to run any time.
     */
    public static function cleanOrphans(): int
    {
        $known = [];
        foreach (['members' => ['photo_path', 'proof_path', 'card_photo_path'], 'member_payments' => ['proof_path']] as $table => $cols) {
            foreach ($cols as $col) {
                foreach (Database::all('SELECT ' . $col . ' AS p FROM ' . $table . ' WHERE ' . $col . ' IS NOT NULL') as $r) {
                    $known[(string) $r['p']] = true;
                }
            }
        }
        $n = 0;
        foreach ([self::PHOTO_DIR, self::PROOF_DIR] as $dir) {
            foreach (glob(STORAGE_PATH . '/private/' . $dir . '/*') ?: [] as $file) {
                $rel = $dir . '/' . basename($file);
                if (is_file($file) && !isset($known[$rel]) && filemtime($file) < time() - 86400 && @unlink($file)) {
                    $n++;
                }
            }
        }
        return $n;
    }

    // ------------------------------------------------------------------------------------------------ small helpers

    private static function ticked(mixed $v): bool
    {
        return is_scalar($v) && in_array(strtolower(trim((string) $v)), ['1', 'on', 'yes', 'true'], true);
    }

    private static function ensureDir(string $dir): void
    {
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new UploadException('The upload folder is not writable.');
        }
    }
}
