<?php
declare(strict_types=1);

namespace App\Services\Membership;

use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Validator;
use App\Models\Member;
use App\Models\MembershipType;
use App\Models\NewsletterDelivery;
use App\Models\Subscriber;

/**
 * The membership APPLICATION: one validation rule set and one creation routine for the public form (processApplication) and for the
 * admin edit screen (updateMember), so the two can never drift. Contract: docs/MEMBERSHIP.md. OWNER: architect (frozen).
 *
 *   $res = MembershipApplication::process($request->all(), ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]);
 *   // closed        -> ['ok'=>false,'closed'=>true,...]
 *   // field errors  -> ['ok'=>false,'errors'=>['email'=>'Please enter a valid email address.', ...]]  (messages come from Pages > Membership)
 *   // duplicate     -> ['ok'=>true,'duplicate'=>true]   (an open application with this e-mail exists: nothing was stored, say nothing more)
 *   // created       -> ['ok'=>true,'duplicate'=>false,'member'=>row,'type'=>type row]
 *
 * PRIVACY: nothing here logs submitted values.
 */
final class MembershipApplication
{
    /** Form field names that are tick boxes (a ticked box posts "1"). */
    public const CHECKBOXES = ['consent_terms', 'consent_privacy', 'declaration', 'consent_updates', 'consent_guardian'];

    /**
     * Validation rules for the public form, for THIS input (the age decides whether the guardian block is required, the "other" answers
     * whether the free-text answers are required, the setting whether the ID number is read). Keys = input names = column names,
     * except the tick boxes (consent_terms, consent_privacy, declaration, consent_updates, consent_guardian).
     * @return array<string,string>
     */
    public static function rules(array $input, bool $forPublic = true): array
    {
        $keys = static fn(string $list): string => implode(',', array_keys(MembershipService::options($list)));
        $dob = self::str($input, 'date_of_birth');
        $minor = MembershipService::isMinor($dob);
        $occOther = self::str($input, 'occupation_status') === MembershipService::OTHER_KEY;
        $heardOther = self::str($input, 'heard_about') === MembershipService::OTHER_KEY;

        $rules = [
            'type_id'                => 'required|integer',
            'full_name'              => 'required|min:2|max:150',
            'gender'                 => 'required|in:' . implode(',', Member::GENDERS),
            'date_of_birth'          => 'required|date',
            'nationality'            => 'required|max:80',
            'email'                  => 'required|email|max:190',
            'phone'                  => 'required|phone|max:40',
            'whatsapp'               => 'nullable|phone|max:40',
            'country'                => 'required|max:80',
            'region'                 => 'required|max:120',
            'city'                   => 'required|max:120',
            'address'                => 'nullable|max:255',
            'occupation_status'      => 'required|in:' . $keys('occupation'),
            'occupation_other'       => ($occOther ? 'required' : 'nullable') . '|max:190',
            'education_level'        => 'required|in:' . $keys('education'),
            'organisation'           => 'nullable|max:190',
            'interests'              => 'nullable|array',
            'skills'                 => 'nullable|max:2000',
            'availability'           => 'required|in:' . $keys('availability'),
            'heard_about'            => 'required|in:' . $keys('heard'),
            'heard_about_other'      => ($heardOther ? 'required' : 'nullable') . '|max:190',
            'motivation'             => 'required|min:' . MembershipService::MOTIVATION_MIN . '|max:3000',
            'guardian_name'          => ($minor ? 'required' : 'nullable') . '|max:150',
            'guardian_phone'         => ($minor ? 'required|phone' : 'nullable|phone') . '|max:40',
            'guardian_relationship'  => ($minor ? 'required' : 'nullable') . '|max:80',
            'emergency_name'         => 'required|max:150',
            'emergency_phone'        => 'required|phone|max:40',
            'emergency_relationship' => 'required|max:80',
            'payment_method'         => 'required|in:' . $keys('payment_methods'),
            'payment_reference'      => 'nullable|max:120',
        ];
        if (MembershipService::collectsId()) {
            $rules['id_number'] = 'nullable|max:60';
        }
        if ($forPublic) {
            // "required|accepted": the Validator skips "accepted" for an empty value, so an unticked box needs "required" to fail
            $rules['consent_terms'] = 'required|accepted';
            $rules['consent_privacy'] = 'required|accepted';
            $rules['declaration'] = 'required|accepted';
            $rules['consent_updates'] = 'nullable|boolean';
            if ($minor) {
                $rules['consent_guardian'] = 'required|accepted';
            }
        }
        return $rules;
    }

    /** @return array<string,string> field => label used in "required" messages */
    private static function labels(): array
    {
        $map = ['type_id' => 'type', 'full_name' => 'name', 'gender' => 'gender', 'date_of_birth' => 'dob', 'nationality' => 'nationality', 'id_number' => 'id_number',
            'email' => 'email', 'phone' => 'phone', 'whatsapp' => 'whatsapp', 'country' => 'country', 'region' => 'region', 'city' => 'city', 'address' => 'address',
            'occupation_status' => 'occupation', 'occupation_other' => 'occupation_other', 'education_level' => 'education', 'organisation' => 'organisation',
            'interests' => 'interests', 'skills' => 'skills', 'availability' => 'availability', 'heard_about' => 'heard', 'heard_about_other' => 'heard_other',
            'motivation' => 'motivation', 'guardian_name' => 'guardian_name', 'guardian_phone' => 'guardian_phone', 'guardian_relationship' => 'guardian_relationship',
            'emergency_name' => 'emergency_name', 'emergency_phone' => 'emergency_phone', 'emergency_relationship' => 'emergency_relationship',
            'payment_method' => 'payment_method', 'payment_reference' => 'payment_reference'];
        $out = [];
        foreach ($map as $field => $key) {
            $out[$field] = rtrim(MembershipService::copy('fields.' . $key . '_label', ucfirst(str_replace('_', ' ', $field))), ' *');
        }
        return $out;
    }

    /** Validator messages built from the editable wording (Pages > Membership > form). @param array<string,string> $rules @return array<string,string> */
    private static function messages(array $rules): array
    {
        $c = static fn(string $k, string $d): string => MembershipService::copy('form.' . $k, $d);
        $map = [
            'required' => $c('err_required', 'Please fill in this field.'), 'email' => $c('err_email', 'Please enter a valid email address.'),
            'phone' => $c('err_phone', 'Please enter a valid phone number.'), 'max' => $c('err_max', 'This is too long. Please shorten it.'),
            'min' => $c('err_min', 'Please write a little more.'), 'in' => $c('err_choice', 'Please choose one of the options.'),
            'integer' => $c('err_choice', 'Please choose one of the options.'), 'date' => $c('err_date', 'Please enter a valid date.'),
            'accepted' => $c('err_accept', 'Please tick this box to continue.'), 'array' => $c('err_choice', 'Please choose one of the options.'),
            'boolean' => $c('err_choice', 'Please choose one of the options.'),
        ];
        $out = [];
        foreach ($rules as $field => $spec) {
            foreach (explode('|', $spec) as $rule) {
                $name = explode(':', $rule, 2)[0];
                if (isset($map[$name])) {
                    // an unticked box is "required" to the Validator but reads "please tick this box"
                    $out[$field . '.' . $name] = $name === 'required' && in_array($field, self::CHECKBOXES, true) ? $map['accepted'] : $map[$name];
                }
            }
        }
        return $out;
    }

    /**
     * Cross-field checks that the Validator cannot express: date-of-birth range, interests, chosen type.
     * @return array<string,string> field => message
     */
    private static function extraErrors(array $input, array $existing): array
    {
        $err = [];
        $c = static fn(string $k, string $d): string => MembershipService::copy('form.' . $k, $d);
        if (!isset($existing['date_of_birth'])) {
            $age = MembershipService::ageOn(self::str($input, 'date_of_birth'));
            if ($age < 0 || $age > 120) {
                $err['date_of_birth'] = $c('err_dob', 'Please check the date of birth.');
            }
        }
        // an address is later used in mailto: links and Reply-To headers: refuse the characters that would let it carry extra parameters
        if (!isset($existing['email']) && preg_match('/[?&=%#"\\\\<>(),;:\\[\\]\\s]/', self::str($input, 'email')) === 1) {
            $err['email'] = $c('err_email', 'Please enter a valid email address.');
        }
        if (!isset($existing['interests']) && isset($input['interests'])) {
            $valid = array_keys(MembershipService::options('interests'));
            foreach ((array) $input['interests'] as $k) {
                if (!is_string($k) || !in_array($k, $valid, true)) {
                    $err['interests'] = $c('err_choice', 'Please choose one of the options.');
                    break;
                }
            }
        }
        return $err;
    }

    /** Collapse whitespace (incl. non-breaking spaces) and drop control and zero-width characters in a single-line value. */
    public static function line(mixed $v): string
    {
        $s = is_scalar($v) ? (string) $v : '';
        $s = (string) preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $s);
        $s = (string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $s);
        return trim((string) preg_replace('/\s+/u', ' ', $s));
    }

    /** Multi-line value: normalise line breaks, drop control and zero-width characters except newline, trim (non-breaking spaces become spaces). */
    public static function text(mixed $v): string
    {
        $s = is_scalar($v) ? str_replace(["\r\n", "\r"], "\n", (string) $v) : '';
        $s = (string) preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}]+/u', '', $s);
        $s = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $s);
        $s = (string) preg_replace('/[^\S\n]+/u', ' ', $s);
        $s = (string) preg_replace('/ ?\n ?/', "\n", $s);
        return trim((string) preg_replace("/\n{3,}/", "\n\n", $s));
    }

    /** A posted value as a trimmed string; '' for anything that is not a plain scalar (an array posted for a text field is never valid). */
    private static function str(array $input, string $key): string
    {
        return is_scalar($input[$key] ?? null) ? trim((string) $input[$key]) : '';
    }

    private const LINE_FIELDS = ['full_name', 'nationality', 'id_number', 'email', 'phone', 'whatsapp', 'country', 'region', 'city', 'address', 'occupation_other',
        'organisation', 'heard_about_other', 'guardian_name', 'guardian_phone', 'guardian_relationship', 'emergency_name', 'emergency_phone',
        'emergency_relationship', 'payment_reference', 'date_of_birth'];
    private const TEXT_FIELDS = ['skills', 'motivation', 'internal_notes'];

    /**
     * Clean the free-text answers BEFORE they are validated, so a value made only of control characters, zero-width or non-breaking spaces
     * counts as empty and fails "required" / "min". Arrays stay arrays (the Validator rejects them); every other field is left alone.
     * @return array<string,mixed>
     */
    private static function normalise(array $input): array
    {
        foreach (self::LINE_FIELDS as $k) {
            if (isset($input[$k]) && !is_array($input[$k])) {
                $input[$k] = self::line($input[$k]);
            }
        }
        foreach (self::TEXT_FIELDS as $k) {
            if (isset($input[$k]) && !is_array($input[$k])) {
                $input[$k] = self::text($input[$k]);
            }
        }
        return $input;
    }

    private static function nullable(string $s): ?string
    {
        return $s === '' ? null : $s;
    }

    /**
     * Validate and store a public application. See the class comment for the result shapes.
     * @param array{ip?:string,user_agent?:string} $meta
     * @return array{ok:bool,closed?:bool,duplicate?:bool,errors?:array<string,string>,member?:array,type?:array}
     */
    public static function process(array $input, array $meta = []): array
    {
        if (!MembershipService::isOpen()) {
            return ['ok' => false, 'closed' => true, 'errors' => ['_form' => MembershipService::copy('form.err_closed', 'Membership registration is closed at the moment.')]];
        }
        $input = self::normalise($input);
        $rules = self::rules($input, true);
        $v = Validator::make($input, $rules, self::messages($rules), self::labels());
        $errors = $v->errors();
        $errors += self::extraErrors($input, $errors);

        $type = null;
        $typeId = is_scalar($input['type_id'] ?? null) ? (int) $input['type_id'] : 0;
        if (!isset($errors['type_id'])) {
            $type = $typeId > 0 ? MembershipType::find($typeId) : null;
            if ($type === null || (int) $type['is_active'] !== 1) {
                $errors['type_id'] = MembershipService::copy('form.err_choice', 'Please choose one of the options.');
                $type = null;
            } elseif (MembershipService::capacity($type)['full']) {
                $errors['type_id'] = MembershipService::copy('form.err_type_full', 'This membership type has no places left. Please choose another.');
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $email = strtolower((string) $input['email']);
        $lock = 'lumina_member_' . substr(sha1($email), 0, 24);
        $locked = (int) Database::value('SELECT GET_LOCK(?, 5)', [$lock], 0) === 1;
        try {
            $open = "'" . implode("','", MembershipService::OPEN_STATUSES) . "'";
            if ((int) Database::value('SELECT COUNT(*) FROM members WHERE email = ? AND status IN (' . $open . ')', [$email], 0) > 0) {
                return ['ok' => true, 'duplicate' => true];
            }
            $row = self::buildRow($input, $type, $meta);
            $id = self::insert($row);
        } finally {
            if ($locked) {
                Database::value('SELECT RELEASE_LOCK(?)', [$lock]);
            }
        }
        if (!empty($row['consent_updates'])) {
            self::subscribe($email, (string) ($meta['ip'] ?? ''));
        }
        MembershipService::flush();
        return ['ok' => true, 'duplicate' => false, 'member' => Member::find($id), 'type' => $type];
    }

    /** Person fields shared by the public application and the staff edit (no type, fee, status, consent or request data). @return array<string,mixed> */
    private static function personRow(array $in): array
    {
        $dob = self::str($in, 'date_of_birth');
        $minor = MembershipService::isMinor($dob);
        $occOther = self::str($in, 'occupation_status') === MembershipService::OTHER_KEY;
        $heardOther = self::str($in, 'heard_about') === MembershipService::OTHER_KEY;
        $interests = [];
        foreach ((array) ($in['interests'] ?? []) as $k) {
            if (is_string($k) && !in_array($k, $interests, true)) {
                $interests[] = $k;
            }
        }
        return [
            'full_name'              => self::line($in['full_name']),
            'gender'                 => (string) $in['gender'],
            'date_of_birth'          => $dob,
            'nationality'            => self::line($in['nationality']),
            'id_number'              => MembershipService::collectsId() ? self::nullable(self::line($in['id_number'] ?? '')) : null,
            'email'                  => strtolower(self::line($in['email'])),
            'phone'                  => self::line($in['phone']),
            'whatsapp'               => self::nullable(self::line($in['whatsapp'] ?? '')),
            'country'                => self::line($in['country']),
            'region'                 => self::line($in['region']),
            'city'                   => self::line($in['city']),
            'address'                => self::nullable(self::line($in['address'] ?? '')),
            'occupation_status'      => (string) $in['occupation_status'],
            'occupation_other'       => $occOther ? self::nullable(self::line($in['occupation_other'] ?? '')) : null,
            'education_level'        => (string) $in['education_level'],
            'organisation'           => self::nullable(self::line($in['organisation'] ?? '')),
            'interests'              => json_encode($interests, JSON_UNESCAPED_UNICODE),
            'skills'                 => self::nullable(self::text($in['skills'] ?? '')),
            'availability'           => (string) $in['availability'],
            'heard_about'            => (string) $in['heard_about'],
            'heard_about_other'      => $heardOther ? self::nullable(self::line($in['heard_about_other'] ?? '')) : null,
            'motivation'             => self::text($in['motivation']),
            'guardian_name'          => $minor ? self::line($in['guardian_name']) : null,
            'guardian_phone'         => $minor ? self::line($in['guardian_phone']) : null,
            'guardian_relationship'  => $minor ? self::line($in['guardian_relationship']) : null,
            'emergency_name'         => self::line($in['emergency_name']),
            'emergency_phone'        => self::line($in['emergency_phone']),
            'emergency_relationship' => self::line($in['emergency_relationship']),
            'payment_method'         => (string) $in['payment_method'],
            'payment_reference'      => self::nullable(self::line($in['payment_reference'] ?? '')),
        ];
    }

    /** @return array<string,mixed> database row for a new member */
    private static function buildRow(array $in, array $type, array $meta): array
    {
        $now = date('Y-m-d H:i:s');
        $minor = MembershipService::isMinor(self::str($in, 'date_of_birth'));
        $ver = MembershipService::consentVersions();
        return MembershipService::feeSnapshot($type) + [
            'type_id'             => (int) $type['id'],
            'status'              => 'pending',
            'consent_guardian_at' => $minor ? $now : null,
            'consent_terms_ver'   => $ver['terms'],
            'consent_privacy_ver' => $ver['privacy'],
            'consent_terms_at'    => $now,
            'consent_privacy_at'  => $now,
            'declaration_at'      => $now,
            'consent_updates'     => in_array(strtolower((string) ($in['consent_updates'] ?? '')), ['1', 'on', 'yes', 'true'], true) ? 1 : 0,
            'consent_ip'          => self::nullable(mb_substr((string) ($meta['ip'] ?? ''), 0, 45)),
            'user_agent'          => self::nullable(mb_substr((string) ($meta['user_agent'] ?? ''), 0, 255)),
        ] + self::personRow($in);
    }

    /** Insert with a fresh random reference; a clash on the UNIQUE index just draws another one. */
    private static function insert(array $row): int
    {
        for ($i = 0; $i < 20; $i++) {
            $row['application_ref'] = MembershipService::generateApplicationRef();
            try {
                return Database::insert('members', $row);
            } catch (\PDOException $e) {
                if ((string) $e->getCode() !== '23000' || !str_contains($e->getMessage(), 'application_ref') && !str_contains($e->getMessage(), 'uq_members_ref')) {
                    throw $e;
                }
            }
        }
        throw new \RuntimeException('Could not allocate an application reference');
    }

    /** Newsletter opt-in ticked on the form. Same policy as the footer form: an address that opted out is never switched back on. */
    private static function subscribe(string $email, string $ip): void
    {
        try {
            if (Subscriber::findBy('email', $email) === null) {
                Subscriber::create([
                    'email' => $email, 'status' => 'subscribed', 'token' => bin2hex(random_bytes(20)), 'source' => 'membership',
                    'ip' => $ip !== '' ? $ip : null, 'subscribed_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\PDOException $e) {
            if ((string) $e->getCode() !== '23000') {
                throw $e;
            }
        }
    }

    // ------------------------------------------------------------------------------------------------ admin edit

    /** Fee snapshot fields a staff edit may set; their old and new values go into the audit entry (amounts are not personal data). */
    private const FEE_FIELDS = ['fee_amount', 'joining_fee', 'fee_currency', 'fee_period'];

    /**
     * Staff correction of a member's details (admin edit screen). Same rules as the public form minus the consents; the type, status and
     * dates are changed through their own actions. Optional keys: internal_notes; and, when $allowFee, the fee snapshot fields
     * (fee_amount, joining_fee, fee_currency, fee_period) used to set a fee that was "to be announced" at application time.
     * A guardian-consent time is NEVER created here (only the applicant's own tick records it); it is cleared when the date of birth
     * makes the member an adult, because there is then no guardian on file. A record whose data was erased cannot be edited.
     * Writes the audit entry with field NAMES only, plus the old and new values of the fee fields and of the consent time.
     * @return array{ok:bool,errors:array<string,string>,member:?array,changed:list<string>}
     */
    public static function update(int $memberId, array $input, ?int $userId = null, bool $allowFee = false): array
    {
        $m = Member::find($memberId);
        if ($m === null) {
            return ['ok' => false, 'errors' => ['_form' => 'Member not found.'], 'member' => null, 'changed' => []];
        }
        if (!empty($m['anonymised_at'])) {
            return ['ok' => false, 'errors' => ['_form' => 'The personal data of this record was erased, so it cannot be edited.'], 'member' => null, 'changed' => []];
        }
        $input = self::normalise($input);
        $rules = self::rules($input, false);
        unset($rules['type_id']);
        $rules['internal_notes'] = 'nullable|max:5000';
        // a staff edit never makes a stored answer "unknown": options the owner removed later stay valid for old records
        foreach (['occupation_status' => 'occupation', 'education_level' => 'education', 'availability' => 'availability', 'heard_about' => 'heard', 'payment_method' => 'payment_methods'] as $field => $list) {
            $keys = array_keys(MembershipService::options($list));
            if (isset($m[$field]) && $m[$field] !== '' && !in_array($m[$field], $keys, true)) {
                $keys[] = (string) $m[$field];
                $rules[$field] = 'required|in:' . implode(',', $keys);
            }
        }
        if ($allowFee) {
            $rules += ['fee_currency' => 'nullable|regex:/^[A-Za-z]{3}$/', 'fee_period' => 'nullable|in:' . implode(',', MembershipService::PERIODS)];
        }
        $v = Validator::make($input, $rules, self::messages($rules), self::labels());
        $errors = $v->errors();
        $errors += self::extraErrors($input, $errors);
        $money = [];                                       // only the fee fields that were actually submitted; '' clears the fee ("to be announced")
        if ($allowFee) {
            foreach (['fee_amount', 'joining_fee'] as $f) {
                if (!array_key_exists($f, $input)) {
                    continue;
                }
                $raw = is_scalar($input[$f]) ? trim((string) $input[$f]) : 'x';
                $n = $raw === '' ? null : MembershipService::parseMoney($raw);
                if ($raw !== '' && $n === null) {
                    $errors[$f] = 'Enter a plain amount such as 50000 or 1500.50, or leave it empty.';
                } else {
                    $money[$f] = $n;
                }
            }
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'member' => null, 'changed' => []];
        }
        $row = self::personRow($input);
        if (!MembershipService::isMinor(self::str($input, 'date_of_birth')) && !empty($m['consent_guardian_at'])) {
            $row['consent_guardian_at'] = null;            // an adult has no guardian on file: the old consent time would be a stale record
        }
        $row['internal_notes'] = self::nullable(self::text($input['internal_notes'] ?? ''));
        if (!MembershipService::collectsId()) {
            unset($row['id_number']);                      // keep a number collected earlier, do not blank it
        }
        if ($allowFee) {
            $row += $money;
            if (trim((string) ($input['fee_currency'] ?? '')) !== '') {
                $row['fee_currency'] = strtoupper(trim((string) $input['fee_currency']));
            }
            if (in_array($input['fee_period'] ?? '', MembershipService::PERIODS, true)) {
                $row['fee_period'] = (string) $input['fee_period'];
            }
        }
        $changed = [];
        foreach ($row as $k => $val) {
            if ((string) ($m[$k] ?? '') !== (string) ($val ?? '')) {
                $changed[] = $k;
            }
        }
        if ($changed !== []) {
            Database::update('members', $row, 'id = ?', [$memberId]);
            $details = ['ref' => $m['application_ref'], 'fields' => $changed];
            foreach (array_merge(self::FEE_FIELDS, ['consent_guardian_at']) as $f) {
                if (in_array($f, $changed, true)) {
                    $details['was_' . $f] = $m[$f] ?? null;
                    $details['now_' . $f] = $row[$f] ?? null;
                }
            }
            AuditLog::record('member.update', 'member', $memberId, 'Edited ' . $m['application_ref'], $details);
            MembershipService::flush();
        }
        return ['ok' => true, 'errors' => [], 'member' => Member::find($memberId), 'changed' => $changed];
    }

    // ------------------------------------------------------------------------------------------------ erasure on request

    /** Statuses in which the personal data may be erased: the member is no longer active (cancel the membership first). */
    public const ERASABLE_STATUSES = ['rejected', 'cancelled', 'expired'];

    /**
     * Erase the personal data of a record on request (for a member who cannot be deleted because of a membership number or receipts).
     * KEEPS: application reference, membership number, type, fee snapshot, status, dates, consent TIMES and versions, and every payment row
     * (amounts, receipt numbers, methods). BLANKS: name, gender, date of birth, nationality, ID number, e-mail, phones, address, occupation,
     * education, organisation, interests, skills, availability, how they heard, motivation, guardian, emergency contact, payment reference,
     * the application's payment reference, internal notes, consent IP and browser. A newsletter row that came from this form is removed.
     * Free-text notes on payments are cleared too (they may name people); payment references stay (financial record). The audit entry carries the reference only.
     * @return array{ok:bool,message:string}
     */
    public static function anonymise(int $memberId, ?int $userId = null): array
    {
        $result = Database::transaction(function () use ($memberId): array {
            $m = Database::one('SELECT * FROM members WHERE id = ? FOR UPDATE', [$memberId]);
            if ($m === null) {
                return ['ok' => false, 'message' => 'Member not found.'];
            }
            if (!empty($m['anonymised_at'])) {
                return ['ok' => false, 'message' => 'The personal data of this record was already erased.'];
            }
            if (!in_array($m['status'], self::ERASABLE_STATUSES, true)) {
                return ['ok' => false, 'message' => 'Cancel or reject the membership first: the personal data of a pending, approved or suspended member cannot be erased.'];
            }
            Database::update('members', [
                'full_name' => 'Erased on request', 'gender' => 'prefer_not_to_say', 'date_of_birth' => '1900-01-01', 'nationality' => '',
                'id_number' => null, 'email' => 'erased-' . (int) $m['id'] . '@invalid.invalid', 'phone' => '', 'whatsapp' => null,
                'country' => '', 'region' => '', 'city' => '', 'address' => null,
                'occupation_status' => '', 'occupation_other' => null, 'education_level' => '', 'organisation' => null,
                'interests' => '[]', 'skills' => null, 'availability' => '', 'heard_about' => '', 'heard_about_other' => null, 'motivation' => null,
                'guardian_name' => null, 'guardian_phone' => null, 'guardian_relationship' => null,
                'emergency_name' => '', 'emergency_phone' => '', 'emergency_relationship' => '',
                'payment_method' => '', 'payment_reference' => null, 'consent_ip' => null, 'user_agent' => null, 'consent_updates' => 0,
                'internal_notes' => null, 'anonymised_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$memberId]);
            Database::execute('UPDATE member_payments SET notes = NULL WHERE member_id = ?', [$memberId]);
            // the newsletter row this form created holds the same address
            $subIds = array_column(Database::all("SELECT id FROM subscribers WHERE source = 'membership' AND email = ?", [(string) $m['email']]), 'id');
            if ($subIds !== []) {
                NewsletterDelivery::erase($subIds, [(string) $m['email']]);        // also out of the e-mail campaign records
            }
            Database::execute("DELETE FROM subscribers WHERE source = 'membership' AND email = ?", [(string) $m['email']]);
            \App\Services\Meetings\MeetingService::erase((string) $m['email'], $memberId);      // meeting invitations / requests that hold the same address (docs/MEETINGS.md section 13)
            return ['ok' => true, 'message' => 'The personal data of ' . $m['application_ref'] . ' was erased. The number, fee and receipts were kept.', 'ref' => $m['application_ref']];
        });
        if ($result['ok']) {
            AuditLog::record('member.anonymise', 'member', $memberId, 'Erased the personal data of ' . $result['ref'], ['ref' => $result['ref'], 'user_id' => $userId]);
            MembershipService::flush();
        }
        return ['ok' => (bool) $result['ok'], 'message' => (string) $result['message']];
    }
}
