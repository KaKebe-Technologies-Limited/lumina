<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Services\Fund\ProposalDocument;
use App\Services\Fund\FundProjects;
use App\Services\Payments\OfficialAccount;

/**
 * FUND A PROJECT: GET /fund-a-project (the proposed projects), GET /fund-a-project/{slug} (one project: the case, the budget and the ways to give), POST /fund-a-project/{slug}/pledge
 * and GET /fund-a-project/{slug}/thanks. A donor picks the whole programme, the Year 1 package, a co-funding share, ONE contribution ("sponsor a component", with a quantity) or any amount;
 * the amount is calculated here from app/config/fundraising/projects.php (never taken from the browser for a fixed choice) and recorded as a PLEDGE in Admin > Donations
 * (a record of intent, never a payment) with the project in the reference and the message, and e-mailed to the notification address.
 *
 * The foundation's OFFICIAL BANK ACCOUNT (App\Services\Payments\OfficialAccount, shown while Admin > Payment options "Bank and manual mobile-money details" is on) is offered to anyone who wants to
 * give straight away: a card on every project page, on the thank-you page (with the reference "<project> DON-<donation id>" and the amount), in the proposal e-mail the visitor asked for, and
 * on the Pesapal and mobile-money pages of a gift that did not go through. No e-mail is sent automatically to a donor who types an address on the pledge form (a public form must not relay mail).
 */
final class FundController extends Controller
{
    use GatewayRedirects;

    private const MANUAL_METHODS = ['bank' => 'Bank transfer', 'mobile_money' => 'Mobile money (we send you the details)', 'paypal' => 'PayPal', 'other' => 'Another way'];

    /**
     * The ways a donor can choose: pay NOW with MTN or Airtel mobile money (when switched on), and the earlier "tell us, we contact you" ways unless they were switched off
     * (Admin > Payment options). When everything is off the donor can still say "another way" and the team makes contact.
     * @return array<string,string>
     */
    private static function methods(): array
    {
        $out = [];
        // the two ways to pay now, in the order set in Admin > Payment options (mobile money first, Pesapal second by default)
        foreach (\App\Services\Payments\PaymentOptions::ranked() as $way) {
            if ($way === 'mobile' && \App\Services\DarazaPay\DarazaPayments::enabled('project')) {
                $out['mobile_now'] = 'Pay now with MTN or Airtel mobile money';
            } elseif ($way === 'online' && \App\Services\Pesapal\PesapalPayments::offered('project')) {
                $out['pesapal_now'] = \App\Services\Pesapal\PesapalPlaces::LABEL;
            }
        }
        if (\App\Services\Payments\PaymentOptions::manual() || $out === []) {
            $out += self::MANUAL_METHODS;
        } else {
            $out['other'] = 'Another way (we contact you)';
        }
        return $out;
    }
    private const CURRENCIES = ['UGX', 'USD', 'EUR', 'GBP'];

    public function index(Request $request): Response
    {
        return $this->render('web/fund/index', ['projects' => FundProjects::all(), 'gets' => FundProjects::partnerGets()], [
            'bodyClass' => 'is-fund',
            'meta' => ['title' => 'Fund a project', 'description' => 'Choose a project of Lumina Africa Foundation in Northern Uganda and fund the whole programme, a launch package or one contribution: a laptop, a scholarship, 100 trees, a clinic day.'],
            'breadcrumbs' => [['Fund a project', null]],
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $p = FundProjects::find($slug) ?? throw new HttpException(404, 'Not found');
        return $this->render('web/fund/show', ['p' => $p, 'others' => array_values(array_filter(FundProjects::all(), static fn(array $o): bool => $o['slug'] !== $slug)), 'methods' => self::methods(), 'currencies' => self::CURRENCIES, 'gets' => FundProjects::partnerGets()], [
            'bodyClass' => 'is-fund is-fund-project',
            'meta' => ['title' => $p['short'] . ': fund this project', 'description' => (string) $p['summary'], 'og_image' => asset('img/projects/' . $p['cover'] . '.jpg')],
            'breadcrumbs' => [['Fund a project', url('fund-a-project')], [(string) $p['short'], null]],
        ]);
    }

    public function pledge(Request $request, string $slug): Response
    {
        $p = FundProjects::find($slug) ?? throw new HttpException(404, 'Not found');
        $back = 'fund-a-project/' . $slug . '#give';
        $in = $request->all();
        $errors = [];

        $choice = FundProjects::parseChoice($p, (string) ($in['choice'] ?? ''), (string) ($in['qty'] ?? '1'));
        if ($choice === null) {
            $errors['choice'] = 'Please choose how you would like to give.';
        }
        $currency = strtoupper(trim((string) ($in['currency'] ?? 'UGX')));
        if (!in_array($currency, self::CURRENCIES, true)) {
            $errors['currency'] = 'Please choose a currency from the list.';
        }
        $amount = null;
        $fixedUnavailable = false;
        if ($choice !== null && !isset($errors['currency'])) {
            if ($choice['fixed']) {
                $amount = FundProjects::fixedAmount($choice, $currency);
                if ($amount === null) {
                    $fixedUnavailable = true;
                    $errors['currency'] = 'This contribution has a set price in UGX or USD. Please choose one of them.';
                }
            } else {
                $parsed = DonateController::parseAmount((string) ($in['amount'] ?? ''));
                if ($parsed === null || $parsed === '' || (float) $parsed < 1 || (float) $parsed > 1000000000) {
                    $errors['amount'] = 'Please type the amount you would like to give, with digits only.';
                } else {
                    $amount = (float) $parsed;
                    $minUsd = (float) ($choice['min_usd'] ?? 0);
                    if ($minUsd > 0 && FundProjects::toUsd($amount, $currency) !== null && FundProjects::toUsd($amount, $currency) < $minUsd) {
                        $errors['amount'] = 'A co-funding share starts at USD ' . number_format($minUsd) . ' (about ' . $currency . ' ' . number_format((float) FundProjects::fromUsd($minUsd, $currency)) . '). Choose "Any amount" to give less.';
                    }
                }
            }
        }

        $rules = [
            'donor_name' => 'required|max:150',
            'donor_email' => 'required|email|max:190',
            'donor_phone' => 'nullable|phone|max:40',
            'method' => 'required|in:' . implode(',', array_keys(self::methods())),
            'message' => 'nullable|max:1500',
        ];
        $v = Validator::make($in, $rules, []);
        $errors += $v->errors();
        if ($errors !== []) {
            $old = $in;
            unset($old['_token'], $old['_hp_ts']);
            throw new ValidationException($errors, $old, $back);
        }
        $data = $v->validated();

        $label = (string) $choice['label'];
        $detail = (string) $choice['detail'];
        $record = [
            'donor_name' => (string) $data['donor_name'],
            'donor_email' => (string) $data['donor_email'],
            'donor_phone' => ($data['donor_phone'] ?? '') === '' ? null : (string) $data['donor_phone'],
            'amount' => round((float) $amount, 2),
            'currency' => $currency,
            'method' => (string) $data['method'],
            'status' => 'pledged',
            'source' => 'pledge_form',
            'reference' => mb_substr('PROJECT:' . $slug . '|' . $choice['code'], 0, 120),
            'message' => "PROJECT PLEDGE: {$p['title']}\nContribution: {$label}" . ($detail !== '' ? "\n{$detail}" : '') . "\n\n" . trim((string) ($data['message'] ?? '')),
            'is_anonymous' => $request->bool('is_anonymous') ? 1 : 0,
            'ip' => $request->ip(),
        ];
        $pay = $record['method'] === 'mobile_now';
        $payPesapal = $record['method'] === 'pesapal_now';
        if ($payPesapal) {
            $record['method'] = 'pesapal';
        }
        $big = false;
        if ($pay) {
            $minor = \App\Services\Shop\Money::parse(number_format((float) $record['amount'], 2, '.', ''));
            $raw = $minor === null ? null : \App\Services\DarazaPay\DarazaPayments::rawUgx($minor, $currency);
            if ($raw !== null && $raw > \App\Services\DarazaPay\DarazaPayments::MAX_UGX) {
                // one mobile-money payment is limited to UGX 5,000,000: a larger gift is recorded and the team arranges it with the donor
                $pay = false;
                $big = true;
                $record['method'] = 'other';
            } elseif ($minor === null || \App\Services\DarazaPay\DarazaPayments::ugx($minor, $currency) === null) {
                $this->fail(['method' => 'Mobile money takes gifts in Uganda shillings (UGX 500 to 5,000,000), or in US dollars when the website has a rate. Please choose UGX.'], $back);
            } else {
                $record['method'] = 'mobile_money';
            }
        }
        if ($payPesapal) {
            $minorP = \App\Services\Shop\Money::parse(number_format((float) $record['amount'], 2, '.', ''));
            if ($minorP === null || $minorP < 1) {
                $this->fail(['amount' => 'Please check the amount.'], $back);
            }
            $donationId = Donation::create($record);
            $r = \App\Services\Pesapal\PesapalPayments::begin('project', (int) $donationId, (int) $minorP, $currency, 'Gift to ' . $p['short'] . ' - Lumina Africa Foundation', ['name' => $record['donor_name'], 'email' => $record['donor_email'], 'phone' => (string) ($record['donor_phone'] ?? '')]);
            if ($r['ok']) {
                \App\Core\Mailer::notify('Project gift started: ' . $p['short'], "A donor started a Pesapal gift to a project (it is recorded as received only when Pesapal confirms the payment).\n\nProject: {$p['title']}\nContribution: {$label}\nAmount: " . number_format((float) $record['amount'], 2) . " {$currency}\nName: {$record['donor_name']}\n\nManage it: " . abs_url('admin/donations') . "\n", $record['donor_email']);
                return $this->sendToGateway((string) $r['link']);
            }
            Flash::error('We could not open the secure payment page just now. Nothing was charged and your gift was saved: we will contact you, or you can try again in a minute.');
            $record['method'] = 'other';
            \App\Core\Database::update('donations', ['method' => 'other'], 'id = ?', [(int) $donationId]);
        } else {
            $donationId = Donation::create($record);
        }
        if ($pay) {
            $b = \App\Services\DarazaPay\DarazaPayments::begin('project', (int) $donationId, (int) $minor, $currency, ['name' => $record['donor_name'], 'email' => $record['donor_email'], 'phone' => (string) ($record['donor_phone'] ?? '')]);
            if ($b['ok']) {
                \App\Core\Mailer::notify('Project gift started: ' . $p['short'], "A donor started a mobile-money gift to a project (it is recorded as received only when the payment is confirmed).\n\nProject: {$p['title']}\nContribution: {$label}\nAmount: " . number_format((float) $record['amount'], 2) . " {$currency}\nName: {$record['donor_name']}\nEmail: {$record['donor_email']}\n\nManage it: " . abs_url('admin/donations') . "\n", $record['donor_email']);
                return $this->redirect((string) $b['url']);
            }
            Flash::error('We could not open the mobile-money page just now. Your gift was saved and we will contact you. You can also try again in a minute.');
        }

        // what the donor writes on a bank transfer so we can match it to this pledge ("Digital Inclusion DON-123"); the stored donations.reference ("PROJECT:...", read by the reports) is not touched
        $ref = FundProjects::bankReference((string) $p['short'], (int) $donationId);

        $body = "New PROJECT gift request (no payment was taken yet)\n" . ($big ? "This amount is above the UGX 5,000,000 limit of one mobile-money payment: please contact the donor to arrange it.\n" : '') . "\n"
            . "Project: {$p['title']}\nContribution: {$label}" . ($detail !== '' ? "\n{$detail}" : '') . "\n"
            . 'Amount: ' . number_format((float) $record['amount'], 2) . " {$record['currency']}\n\n"
            . "Name: {$record['donor_name']}" . ($record['is_anonymous'] ? " (asked to stay anonymous)\n" : "\n")
            . "Email: {$record['donor_email']}\nPhone: " . ($record['donor_phone'] ?? '-') . "\nMethod: {$record['method']}\n"
            . "Reference quoted to the donor for a bank transfer: {$ref}\n\n"
            . "Message:\n" . (trim((string) ($data['message'] ?? '')) !== '' ? trim((string) $data['message']) : '-') . "\n\nManage it in the admin area: " . abs_url('admin/donations') . "\n";
        if (!Mailer::notify('New project pledge: ' . $p['short'], $body, $record['donor_email'])) {
            Log::warning('Project pledge notification e-mail could not be sent', ['email' => $record['donor_email']]);
        }

        Session::put('fund_thanks', ['slug' => $slug, 'label' => $label, 'detail' => $detail, 'amount' => (float) $record['amount'], 'currency' => $currency, 'method' => (string) $record['method'], 'name' => (string) $record['donor_name'], 'big' => $big, 'ref' => $ref]);
        return $this->redirect('fund-a-project/' . $slug . '/thanks');
    }

    /**
     * POST /fund-a-project/{slug}/proposal: "Request the full proposal". The visitor picks which proposal (any of the funded projects, or all of them; the project they are looking at is preselected).
     * Records the request in Admin > Messages, tells the team, and e-mails the proposal PDF(s) at once (built from the project page, see ProposalDocument). Spam traps: honeypot + throttle on the
     * route, and at most two sends per e-mail address and choice per day.
     */
    public function proposal(Request $request, string $slug): Response
    {
        $here = FundProjects::find($slug) ?? throw new HttpException(404, 'Not found');
        if (($here['status'] ?? '') !== 'proposal') {
            throw new HttpException(404, 'Not found');
        }
        $back = 'fund-a-project/' . $slug . '#proposal';
        $offered = array_values(array_filter(FundProjects::all(), static fn(array $p): bool => ($p['status'] ?? '') === 'proposal'));
        $slugs = array_column($offered, 'slug');
        $rules = [
            'pr_project' => 'required|in:' . implode(',', array_merge($slugs, ['all'])),
            'pr_name' => 'required|max:150', 'pr_email' => 'required|email|max:190', 'pr_phone' => 'nullable|phone|max:40', 'pr_org' => 'nullable|max:150', 'pr_note' => 'nullable|max:1500',
        ];
        $d = $this->validate($rules, ['pr_project.required' => 'Please choose which proposal you would like.', 'pr_project.in' => 'Please choose a proposal from the list.', 'pr_name.required' => 'Please tell us your name.', 'pr_email.required' => 'Please type your e-mail address: the proposal is sent there.', 'pr_email.email' => 'That e-mail address does not look right.'],
            ['pr_project' => 'Proposal', 'pr_name' => 'Name', 'pr_email' => 'E-mail', 'pr_phone' => 'Phone', 'pr_org' => 'Organisation', 'pr_note' => 'Message'], $back);
        $choice = (string) $d['pr_project'];
        $targets = $choice === 'all' ? $offered : array_values(array_filter($offered, static fn(array $p): bool => $p['slug'] === $choice));
        $name = trim((string) $d['pr_name']);
        $email = strtolower(trim((string) $d['pr_email']));
        $phone = trim((string) ($d['pr_phone'] ?? ''));
        $org = trim((string) ($d['pr_org'] ?? ''));
        $note = trim((string) ($d['pr_note'] ?? ''));
        $label = $choice === 'all' ? 'all ' . count($targets) . ' project proposals' : (string) $targets[0]['short'];

        if (!RateLimit::attempt('proposal:' . hash('sha256', $email . '|' . $choice), 2, 86400)) {
            Flash::info('We have already sent this proposal to ' . $email . '. Please check your inbox and spam folder, or write to us and we will send it again.');
            return $this->redirect($back);
        }

        $site = (string) setting('site_name', 'Lumina Africa Foundation');
        $team = (string) setting('email_info', setting('email_primary', ''));
        $titles = implode('", "', array_column($targets, 'title'));
        $id = ContactMessage::create([
            'name' => $name, 'email' => $email, 'phone' => $phone !== '' ? $phone : null,
            'subject' => 'Full proposal request: ' . $label,
            'message' => 'Asked for the full proposal of "' . $titles . '".' . ($org !== '' ? "\nOrganisation: " . $org : '') . ($note !== '' ? "\n\n" . $note : '') . "\n\n(The proposal PDF was e-mailed to the visitor automatically.)",
            'status' => 'new', 'ip' => $request->ip(), 'user_agent' => $request->userAgent(),
        ]);

        $sent = false;
        try {
            $files = [];
            foreach ($targets as $t) {
                $files[] = ['filename' => ProposalDocument::filename($t), 'content' => ProposalDocument::pdf($t, $name, $org), 'type' => 'application/pdf'];
            }
            $first = trim((string) strtok($name, ' '));
            $many = count($targets) > 1;
            $text = 'Dear ' . ($first !== '' ? $first : 'friend') . ",\n\n"
                . 'Thank you for your interest in ' . ($many ? 'our projects' : '"' . $targets[0]['title'] . '"') . '. Your support, or even a few minutes of your attention, means a great deal to the young people and families we serve.' . "\n\n"
                . ($many ? 'The ' . count($targets) . " full proposals are attached to this e-mail (PDF):\n- " . implode("\n- ", array_column($targets, 'title')) . "\n\n" : "The full proposal is attached to this e-mail (PDF). ")
                . 'It sets out the need, what the project delivers, why it is a sound choice, the budget and every way to give.' . "\n\n"
                . 'When you are ready, you can give or pledge here: ' . abs_url('fund-a-project' . ($many ? '' : '/' . $targets[0]['slug'])) . "\n\n"
                . (OfficialAccount::visible() ? 'To give by bank transfer, send your gift to our account and quote ' . ($many ? 'the name of the project you are funding' : '"' . $targets[0]['short'] . '"') . " as the payment reference:\n\n" . OfficialAccount::text() . "\n\n" : '')
                . 'If you would like the detailed budget, a conversation or a visit, just reply to this e-mail' . ($team !== '' ? ' or write to ' . $team : '') . ".\n\n"
                . "With gratitude,\n" . $site . "\n" . abs_url('') . "\n";
            $sent = Mailer::send($email, $many ? 'Your full proposals: ' . $site : 'Your full proposal: ' . $targets[0]['title'], $text, ['attachments' => $files, 'reply_to' => $team !== '' ? $team : null]);
        } catch (\Throwable $e) {
            Log::warning('Proposal e-mail failed: ' . $e::class);
        }

        Mailer::notify('Full proposal requested: ' . $label, implode("\n", [
            'Name: ' . $name, 'Email: ' . $email, 'Phone: ' . ($phone !== '' ? $phone : '-'), 'Organisation: ' . ($org !== '' ? $org : '-'), 'Project: ' . $titles,
            'Proposal e-mailed automatically: ' . ($sent ? 'yes' : 'NO, please send it by hand'), '', $note, '', 'Open in the admin: ' . abs_url('admin/messages/' . $id),
        ]), $email);

        Flash::success($sent
            ? 'Thank you, ' . $name . '! We have e-mailed ' . (count($targets) > 1 ? 'the full proposals' : 'the full proposal') . ' to ' . $email . '. Please check your inbox (and the spam folder).'
            : 'Thank you, ' . $name . '! We have your request and the team will e-mail ' . (count($targets) > 1 ? 'the full proposals' : 'the full proposal') . ' to ' . $email . ' very soon.');
        return $this->redirect($back);
    }

    public function thanks(Request $request, string $slug): Response
    {
        $p = FundProjects::find($slug) ?? throw new HttpException(404, 'Not found');
        $t = (array) Session::get('fund_thanks', []);
        if (($t['slug'] ?? '') !== $slug) {
            return $this->redirect('fund-a-project/' . $slug);
        }
        return $this->render('web/fund/thanks', ['p' => $p, 't' => $t, 'methods' => self::methods() + self::MANUAL_METHODS], [
            'bodyClass' => 'is-fund',
            'meta' => ['title' => 'Thank you for your pledge', 'robots' => 'noindex,nofollow'],
            'breadcrumbs' => [['Fund a project', url('fund-a-project')], [(string) $p['short'], url('fund-a-project/' . $slug)], ['Thank you', null]],
        ]);
    }
}
