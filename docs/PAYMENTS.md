# Payments

One place to understand how money enters and leaves the site. Detail for single features lives in `SHOP.md`, `PESAPAL.md`, `MEMBERSHIP.md`, `ACADEMY.md`.

## The ways to pay

| Way | Code | Ledger table | Notes |
|---|---|---|---|
| Mobile money (MTN / Airtel) through **Daraza** | `app/services/DarazaPay/` | `daraza_payments` | The default and, today, the only way switched on. Collects (`in`) and pays out (`out`). UGX only, UGX 500 to 5,000,000 per payment |
| Card / online through **Pesapal** | `app/services/Pesapal/` | `pesapal_payments` | Donations, Academy fees. Off until switched back on |
| **Flutterwave** (shop) | `app/services/Shop/FlutterwaveClient.php` | `shop_payments` | Shop only. See `SHOP.md` |
| Payment link, bank / manual details, "other" | `app/services/Payments/PaymentOptions.php` | `donations`, `member_payments`, `shop_payments` | Shown as instructions; staff record the money by hand |

**Admin > Payment options** (`Admin\DarazaApiController`, `Payments\PaymentOptions`) chooses which ways visitors see, and in what order. The switches are
settings: `pay_opt_online`, `pay_opt_link`, `pay_opt_manual`, `pay_opt_other`, `pay_rank_<way>`, and `daraza_api_<purpose>_on` per purpose.
The "Mobile money only" preset turns the others off (it refuses while no Daraza key is set); "Restore" turns them back on.
Every public payment screen asks `PaymentOptions` what to show: do not hard-code a way in a view.

Purposes (`DarazaPayments::PURPOSES`): donation, project, shop, membership, academy_signup, academy_cert, payout. Each purpose has its own on/off switch.

## Setup (Daraza)

1. Create an app in the Daraza developer console and copy its API key.
2. On the server, in `.env` (never in the repo, never in chat): `DARAZA_API_KEY=...` and `DARAZA_ENV=sandbox` (or `live`).
3. Admin > Mobile money (Daraza API): the Connection card shows whether the key is read and whether Daraza answers. Test in sandbox first.
4. Press "Mobile money only" in Admin > Payment options when ready.

Troubleshooting: the Connection card shows the reason (missing key, wrong environment, key rejected). The server log is `storage/logs/`.

## How a mobile-money payment works

`DarazaPayments::begin*()` creates a `daraza_payments` row (`pending`) and an audit event. `collect()` calls Daraza's request-to-pay **and waits** until the payer
approves on their phone (up to 5 minutes), then `apply()` finishes the purpose (marks the order paid, issues the receipt, enrols the learner, ...) and sends the
thank-you e-mail. Status values: `pending`, `collecting`, `paid`, `failed`, `unknown`.

`unknown` / "Waiting" means the answer was lost (for example the host cut the long request). Money may have arrived. Admin > Payments > **Resolve**:

* **Check again** asks Daraza for the status by request id.
* **Money received: confirm** completes it by hand (only after seeing the money in the Daraza wallet or statement). Logged with the admin and a note.
* **Not received: close** marks it failed.
* **Remind the payer by e-mail** (`PaymentReminders`, at most one per payment per day) and **Delete the record** (never a paid one; a waiting one only after an hour).

An automatic confirmation needs Daraza's webhook; it is not built because the webhook payload format was not available. Ask for Daraza's webhook documentation first.

Rules that must not break:

* Amounts are minor units (`Money`, scale 100). Daraza gets whole UGX (`DarazaPayments::ugx()`).
* A payment is applied **once**: `DarazaPayments::pay()` locks the row (`SELECT ... FOR UPDATE`), moves it to `paid`, and only the caller that made that change runs `apply()`. Keep any new way of confirming a payment going through `pay()`.
* `CREATE TABLE` inside a transaction silently commits it. Create tables first (`PaymentEvents::install()` checks `tableExists` before any transaction).
* Never log a PIN, API key, card number or a full phone number (`PaymentEvents::maskPhone()`).

## Shop: pay now or pay on delivery

`Shop\CodDeposit`: pay on delivery asks for a deposit (setting `shop_cod_deposit_pct`, default 60) paid by mobile money **before** the order is made, rounded up to UGX 100 and
never above the total. While the deposit cannot be paid (no key, or switched off) pay on delivery is hidden unless the percentage is 0. `PaymentService::markPaid` confirms the order
when the deposit arrives; the rest is collected on delivery.

## Dashboard and audit trail

* **Admin > Payments** (`Admin\PaymentsController`, `Payments\PaymentsDashboard`): every payment of every ledger in one list (`union()`), filters (range, channel, category, status, direction), CSV export, balance, send money (payout).
* **Admin > Payments > Audit trail** (`Payments\PaymentEvents`, table `payment_events`, created on first use): one line per step: `started`, `requested`, `success`, `failed`, `unknown`, `reversed`, `review`, `thanks`, `reminded`, `deleted`.
  Writing to it never breaks a payment (errors are swallowed and logged). Add an event name to `PaymentEvents::EVENTS` and `EVENT_LABELS` to add a step.
* **Main admin dashboard**: payment rings by category and the Daraza collections table (who, time, country from the phone's dialling code, category). Visible to users with `settings.manage`.
* **Thank-you e-mail** (`PaymentThanks`): sent once per reference for every successful non-shop payment (the shop has its own order e-mails).

## Adding a new purpose or way to pay

1. Add the purpose to `DarazaPayments::PURPOSES` (and `PaymentEvents::category()` mapping, and a `daraza_api_<purpose>_on` setting).
2. Create the row at the start of the flow (`begin*`), finish it in `apply()`, and call `PaymentEvents::log()` at each step.
3. Show the ways with `PaymentOptions` (never hard-code).
4. Add checks to `bin/selftest-daraza.php` and `bin/selftest-payments-dashboard.php`; run `php bin/test.php --group=smoke`.

## Tests

`payment-options`, `payments-dashboard`, `daraza`, `daraza-api`, `pesapal`, `shop`, `fund`, `membership-pay`, `academy-payments` (see `TESTING.md`).
Daraza is replaced by a fake client, so no test moves money.
