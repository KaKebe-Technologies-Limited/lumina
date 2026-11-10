<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\AmbassadorApplication;
use App\Core\Uploader;
use App\Core\UploadException;
use App\Services\Recruitment\Recruitment;
use App\Services\Web\Countries;

/**
 * GET /involvement/ambassadors and POST /involvement/ambassadors: the call for ambassadors (people anywhere in the world who raise funds and lobby for the
 * foundation) and its sign-up form. Applications are stored in `ambassador_applications` (Admin > Inbox > Ambassadors) and e-mailed to the notification address
 * (Settings); the applicant gets a confirmation. The route adds the honeypot + throttle middleware; every visible string comes from the "ambassadors" registry page.
 */
final class AmbassadorsController extends Controller
{
    private const BACK = 'involvement/ambassadors#apply';

    private function ready(): void
    {
        try {
            if (!Database::tableExists('ambassador_applications')) {
                throw new HttpException(404, 'Not found');
            }
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable) {
            throw new HttpException(404, 'Not found');
        }
    }

    public function index(Request $request): Response
    {
        $this->ready();
        $directory = $this->directory();
        return $this->renderPage('ambassadors', 'web/ambassadors/index', [
            'regions'    => Countries::REGIONS,
            'countries'  => Countries::byRegion(),
            'currencies' => Countries::CURRENCIES,
            'directory'  => $directory['rows'],
            'countryCount' => $directory['countries'],
            'faqs'       => HomeSupport::faqs('ambassadors', null, 'faq.items'),
        ], [
            'bodyClass'   => 'is-ambassadors',
            'breadcrumbs' => [[HomeSupport::navLabel('involvement', 'Involvement'), url('involvement')], [HomeSupport::navLabel('ambassadors', 'Ambassadors'), null]],
        ]);
    }

    public function submit(Request $request): Response
    {
        $this->ready();
        $in = $request->all();
        $rules = [
            'name'         => 'required|max:150',
            'email'        => 'required|email|max:190',
            'phone'        => 'nullable|phone|max:40',
            'region'       => 'required|in:' . implode(',', array_keys(Countries::REGIONS)),
            'country'      => 'required|max:80',
            'city'         => 'nullable|max:120',
            'occupation'   => 'nullable|max:190',
            'organisation' => 'nullable|max:190',
            'profile_url'  => 'nullable|url|max:300',
            'availability' => 'nullable|in:' . implode(',', array_keys(AmbassadorApplication::AVAILABILITY)),
            'network'      => 'nullable|max:3000',
            'motivation'   => 'required|max:3000',
            'inkind_notes' => 'nullable|max:3000',
            'other_text'   => 'nullable|max:3000',
        ];
        $labels = ['name' => 'Your name', 'email' => 'E-mail address', 'region' => 'Region', 'country' => 'Country', 'motivation' => 'Why you would like to be an ambassador',
            'profile_url' => 'Profile link', 'availability' => 'Availability'];
        $v = Validator::make($in, $rules, [], $labels);
        $errors = $v->fails() ? $v->errors() : [];

        $region = (string) ($in['region'] ?? '');
        $country = trim((string) ($in['country'] ?? ''));
        if (!isset($errors['region']) && !isset($errors['country']) && !Countries::valid($region, $country)) {
            $errors['country'] = 'Please choose a country from the list for the region you selected.';
        }

        $pick = static function (mixed $raw, array $allowed): array {
            $out = [];
            foreach (is_array($raw) ? $raw : [] as $x) {
                if (is_string($x) && isset($allowed[$x])) {
                    $out[$x] = true;
                }
            }
            return array_keys($out);
        };
        $roles = $pick($in['roles'] ?? [], AmbassadorApplication::ROLES);
        if ($roles === []) {
            $errors['roles'] = 'Choose at least one way in which you could help.';
        }
        $pledges = $pick($in['pledge_types'] ?? [], AmbassadorApplication::PLEDGES);
        if ($pledges === []) {
            $errors['pledge_types'] = 'Choose what you can pledge each year: money, in-kind support, something else, or a mix.';
        }
        $amount = null;
        $currency = null;
        if (in_array('money', $pledges, true)) {
            $raw = str_replace([',', ' '], '', trim((string) ($in['annual_amount'] ?? '')));
            if ($raw === '' || !is_numeric($raw) || (float) $raw <= 0) {
                $errors['annual_amount'] = 'Enter the amount you can raise or give in a year (a number greater than zero).';
            } elseif ((float) $raw > 1000000000000) {
                $errors['annual_amount'] = 'That amount is too large. Please check it.';
            } else {
                $amount = round((float) $raw, 2);
            }
            $currency = (string) ($in['currency'] ?? '');
            if (!isset(Countries::CURRENCIES[$currency])) {
                $errors['currency'] = 'Choose the currency of your amount.';
            }
        }
        $items = $pick($in['inkind_items'] ?? [], AmbassadorApplication::INKIND);
        $inkindNotes = trim((string) ($in['inkind_notes'] ?? ''));
        if (in_array('inkind', $pledges, true) && $items === [] && $inkindNotes === '') {
            $errors['inkind_items'] = 'Tell us which items you can collect or give (tick them, or describe them in the box).';
        }
        $other = trim((string) ($in['other_text'] ?? ''));
        if (in_array('other', $pledges, true) && $other === '') {
            $errors['other_text'] = 'Tell us what else you can lobby for or give.';
        }
        if (empty($in['consent'])) {
            $errors['consent'] = 'Please tick the box so we can store your details and contact you.';
        }
        $email = strtolower(trim((string) ($in['email'] ?? '')));
        if (!isset($errors['email']) && $email !== '' && (int) Database::value(
            "SELECT COUNT(*) FROM ambassador_applications WHERE email = ? AND status IN ('new','reviewing','on_hold') AND created_at >= (NOW() - INTERVAL 60 DAY)", [$email], 0) > 0) {
            $errors['email'] = 'We already have an application from this e-mail address and will be in touch. Write to us if you need to change something.';
        }
        Recruitment::install();
        // the CV and the portrait are required
        $cvFile = $request->file('cv');
        $photoFile = $request->file('portrait');
        if ($cvFile === null) {
            $errors['cv'] = 'Please attach your CV (PDF or Word).';
        }
        $problem = Recruitment::portraitProblem($photoFile);
        if ($problem !== '') {
            $errors['portrait'] = $problem;
        }
        if ($errors !== []) {
            $this->fail($errors, self::BACK);
        }
        try {
            $cv = Uploader::privateFile($cvFile, 'cv', (array) config('upload.cv_exts'));
        } catch (UploadException $e) {
            $this->fail(['cv' => $e->getMessage()], self::BACK);
        }
        $pic = Recruitment::storePortrait($photoFile);
        if (!$pic['ok']) {
            Uploader::deletePrivate($cv['path']);
            $this->fail(['portrait' => $pic['error']], self::BACK);
        }

        $text = static fn(string $k, int $max): ?string => ($s = mb_substr(trim(strip_tags((string) ($in[$k] ?? ''))), 0, $max)) !== '' ? $s : null;
        $record = [
            'name' => mb_substr(trim(strip_tags((string) $in['name'])), 0, 150), 'email' => $email, 'phone' => $text('phone', 40), 'region' => $region, 'country' => $country,
            'city' => $text('city', 120), 'occupation' => $text('occupation', 190), 'organisation' => $text('organisation', 190), 'profile_url' => $text('profile_url', 300),
            'roles' => json_encode($roles), 'pledge_types' => implode(',', $pledges), 'annual_amount' => $amount, 'currency' => $currency,
            'inkind_items' => $items !== [] ? json_encode($items) : null, 'inkind_notes' => $text('inkind_notes', 3000), 'other_text' => $text('other_text', 3000),
            'network' => $text('network', 3000), 'motivation' => $text('motivation', 3000), 'availability' => $text('availability', 80),
            'public_ok' => !empty($in['public_ok']) ? 1 : 0, 'consent_at' => date('Y-m-d H:i:s'), 'status' => 'new', 'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
        ];
        $record += ['cv_path' => $cv['path'], 'cv_original_name' => $cv['name'], 'cv_mime' => $cv['mime'], 'cv_size' => $cv['size'], 'photo_path' => $pic['path']];
        try {
            $id = AmbassadorApplication::create($record);
        } catch (\Throwable $e) {
            Uploader::deletePrivate($cv['path']);
            Uploader::deletePrivate($pic['path']);
            throw $e;
        }
        $ref = Recruitment::assignRef('ambassador', (int) $id);
        $record['ref_no'] = $ref;

        $this->mail($record, (int) $id);
        Flash::success((string) content('ambassadors', 'form.success') . ($ref !== '' ? ' Your application number is ' . $ref . '. We have e-mailed you a private link to your ambassador profile, where you can follow your application.' : ''));
        return $this->redirect('involvement/ambassadors#apply');
    }

    /** The confirmation for the applicant and the notice for the team. @param array<string,mixed> $r */
    private function mail(array $r, int $id): void
    {
        $pledge = [];
        if ($r['annual_amount'] !== null) {
            $pledge[] = 'Money: ' . number_format((float) $r['annual_amount'], 2) . ' ' . $r['currency'] . ' per year';
        }
        if (in_array('inkind', explode(',', (string) $r['pledge_types']), true)) {
            $names = array_map(static fn(string $k): string => AmbassadorApplication::INKIND[$k] ?? $k, AmbassadorApplication::jsonList($r['inkind_items']));
            $pledge[] = 'In kind: ' . ($names ? implode(', ', $names) : '-') . ($r['inkind_notes'] ? "\n  " . $r['inkind_notes'] : '');
        }
        if ($r['other_text']) {
            $pledge[] = 'Other: ' . $r['other_text'];
        }
        $roles = implode('; ', array_map(static fn(string $k): string => AmbassadorApplication::ROLES[$k] ?? $k, AmbassadorApplication::jsonList($r['roles'])));
        $regionName = Countries::REGIONS[$r['region']] ?? $r['region'];
        $body = "New ambassador application" . (!empty($r['ref_no']) ? ' (' . $r['ref_no'] . ')' : '') . "\n\nName: {$r['name']}\nEmail: {$r['email']}\nPhone: " . ($r['phone'] ?? '-') . "\nLocation: " . ($r['city'] ? $r['city'] . ', ' : '') . "{$r['country']} ({$regionName})\n"
            . 'Occupation: ' . ($r['occupation'] ?? '-') . ' / ' . ($r['organisation'] ?? '-') . "\n\nCan help by: {$roles}\n\nYearly pledge:\n" . implode("\n", $pledge ?: ['-'])
            . "\n\nWhy: " . ($r['motivation'] ?? '-') . "\n\nOpen it in the admin area: " . abs_url('admin/ambassadors/' . $id) . "\n";
        if (!Mailer::notify('New ambassador application: ' . $r['name'] . ' (' . $r['country'] . ')', $body, (string) $r['email'])) {
            Log::warning('Ambassador notification e-mail could not be sent', ['id' => $id]);
        }
        Recruitment::received('ambassador', $id);
    }

    /** Approved ambassadors that agreed to be shown, and the number of countries they represent. @return array{rows:list<array<string,mixed>>,countries:int} */
    private function directory(): array
    {
        try {
            $rows = Database::all(
                "SELECT id, name, country, region, occupation, organisation FROM ambassador_applications WHERE status = 'approved' AND public_ok = 1 ORDER BY reviewed_at DESC, id DESC LIMIT 60"
            );
            $countries = (int) Database::value("SELECT COUNT(DISTINCT country) FROM ambassador_applications WHERE status = 'approved'", [], 0);
            return ['rows' => $rows, 'countries' => $countries];
        } catch (\Throwable) {
            return ['rows' => [], 'countries' => 0];
        }
    }
}
