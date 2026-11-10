<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\Log;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Donation;
use App\Services\Fund\FundProjects;

/**
 * FUND A PROJECT: GET /fund-a-project (the proposed projects), GET /fund-a-project/{slug} (one project: the case, the budget and the ways to give), POST /fund-a-project/{slug}/pledge
 * and GET /fund-a-project/{slug}/thanks. A donor picks the whole programme, the Year 1 package, a co-funding share, ONE contribution ("sponsor a component", with a quantity) or any amount;
 * the amount is calculated here from app/config/fundraising/projects.php (never taken from the browser for a fixed choice) and recorded as a PLEDGE in Admin > Donations
 * (a record of intent, never a payment) with the project in the reference and the message, and e-mailed to the notification address.
 */
final class FundController extends Controller
{
    private const MANUAL_METHODS = ['bank' => 'Bank transfer', 'mobile_money' => 'Mobile money (we send you the details)', 'paypal' => 'PayPal', 'other' => 'Another way'];

    /**
     * The ways a donor can choose: pay NOW with MTN or Airtel mobile money (when switched on), and the earlier "tell us, we contact you" ways unless they were switched off
     * (Admin > Payment options). When everything is off the donor can still say "another way" and the team makes contact.
     * @return array<string,string>
     */
    private static function methods(): array
    {
        $out = [];
        if (\App\Services\DarazaPay\DarazaPayments::enabled('project')) {
            $out['mobile_now'] = 'Pay now with MTN or Airtel mobile money';
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
        $donationId = Donation::create($record);
        if ($pay) {
            $b = \App\Services\DarazaPay\DarazaPayments::begin('project', (int) $donationId, (int) $minor, $currency, ['name' => $record['donor_name'], 'email' => $record['donor_email'], 'phone' => (string) ($record['donor_phone'] ?? '')]);
            if ($b['ok']) {
                \App\Core\Mailer::notify('Project gift started: ' . $p['short'], "A donor started a mobile-money gift to a project (it is recorded as received only when the payment is confirmed).\n\nProject: {$p['title']}\nContribution: {$label}\nAmount: " . number_format((float) $record['amount'], 2) . " {$currency}\nName: {$record['donor_name']}\nEmail: {$record['donor_email']}\n\nManage it: " . abs_url('admin/donations') . "\n", $record['donor_email']);
                return $this->redirect((string) $b['url']);
            }
            Flash::error('We could not open the mobile-money page just now. Your gift was saved and we will contact you. You can also try again in a minute.');
        }

        $body = "New PROJECT gift request (no payment was taken yet)\n" . ($big ? "This amount is above the UGX 5,000,000 limit of one mobile-money payment: please contact the donor to arrange it.\n" : '') . "\n"
            . "Project: {$p['title']}\nContribution: {$label}" . ($detail !== '' ? "\n{$detail}" : '') . "\n"
            . 'Amount: ' . number_format((float) $record['amount'], 2) . " {$record['currency']}\n\n"
            . "Name: {$record['donor_name']}" . ($record['is_anonymous'] ? " (asked to stay anonymous)\n" : "\n")
            . "Email: {$record['donor_email']}\nPhone: " . ($record['donor_phone'] ?? '-') . "\nMethod: {$record['method']}\n\n"
            . "Message:\n" . (trim((string) ($data['message'] ?? '')) !== '' ? trim((string) $data['message']) : '-') . "\n\nManage it in the admin area: " . abs_url('admin/donations') . "\n";
        if (!Mailer::notify('New project pledge: ' . $p['short'], $body, $record['donor_email'])) {
            Log::warning('Project pledge notification e-mail could not be sent', ['email' => $record['donor_email']]);
        }

        Session::put('fund_thanks', ['slug' => $slug, 'label' => $label, 'detail' => $detail, 'amount' => (float) $record['amount'], 'currency' => $currency, 'method' => (string) $record['method'], 'name' => (string) $record['donor_name'], 'big' => $big]);
        return $this->redirect('fund-a-project/' . $slug . '/thanks');
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
