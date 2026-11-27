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

## Official bank account (Donate, shop, project gifts)

The foundation's bank account (Lumina Africa Foundation, account number, Pearl Bank, Gulu) is shown to people who want to pay by bank transfer, as **one source of truth**: `Payments\OfficialAccount`, one reusable card `partials/bank-card.php` (`scss/components/_bank-card.scss`, `assets/js/bank-card.js`: a Copy button on every row, shown by the script only; the numbers are plain selectable text).

* **Where the values live**: the settings `official_bank_account_name`, `official_bank_account_number`, `official_bank_name`, `official_bank_branch`, `official_bank_swift` (empty = not shown) and `official_bank_note` in **Settings > Donations**, also editable in the "Your account details" box on Admin > Donations. Their **defaults are written only in `app/config/settings_schema.php`**: the account shows right after a deploy with no database import, and `Settings::get` returns an explicitly saved empty string instead of a default, so `OfficialAccount::value()` falls back to the schema default whenever a saved value is empty (an administrator who saves blank boxes can never blank the account). **To correct a digit, change it in Settings (or the schema default for a fresh install): nowhere else.**
* **Extra instructions**: `donate_bank_details` is now only "Extra bank instructions" (for example "use your full name as the reference"), printed under the account and **not repeated** when it already contains the official account number.
* **Hide it everywhere**: Admin > Payment options > "Bank and manual mobile-money details" (`PaymentOptions::manual()`) off hides the card on every page (`OfficialAccount::visible()` is the single gate for the official account's card; a shop that types its own account number is gated separately by `PaymentMethods::bankOffer()`, which applies the same switch). E-mails sent by a person at the foundation (the pledge reminder) still list it; `OfficialAccount::text()` does not check the switch.
* **Reference to quote** (depends on the page): `DON-<donation id>` for a donation pledge (also written into `donations.reference` when it is empty; a `PROJECT:...` reference is never overwritten); `<project name> DON-<id>` for a project gift **once a pledge exists** (the project thank-you page, the pledge reminder e-mail, the mobile-money page); only `<project name>` where no pledge exists or it was cancelled (the card on the project page, the proposal e-mail, the Pesapal failed/reversed page); the order number for a shop order. A `DON-<id>` matches the receipt and Admin > Payments.
* **Where it appears**:
  * **Donate page** (`/donate`): the card in the "ways to give" grid, the extra instructions under it, a "Prefer a bank transfer? See our bank account" line under the give-online buttons. The order of the ways to give is fixed (only the give-online buttons follow Payment options > order).
  * **After a pledge**: `POST /donate/pledge` redirects to `GET /donate/thanks` (`DonateController::thanks`, session `donate_thanks`, read with `Session::get` so a refresh works, expires after an hour, noindex, `private, no-store` through the `/donate` prefix of `Cloudflare::NO_STORE_PREFIXES`): the thank-you, "no money has been taken yet", the card with the reference and the amount, mobile-money and PayPal details when set, what happens next. **No automatic e-mail goes to the address a visitor types on a public form** (mail-relay risk); the staff pledge e-mail carries the reference.
  * **Fund a project**: the card below the pledge form on every project page (also "coming" projects), the thank-you page (reference `<project> DON-<id>` and the amount), the "Request the full proposal" e-mail (account and reference while the card is visible). The proposal **PDF deliberately does not carry the account** (it is forwarded and cannot be corrected).
  * **Payment result pages**: the Pesapal return page shows the card only for a **failed or reversed** donation or project gift; the mobile-money page (`/pay/mobile-money/{ref}`) has a collapsed "Prefer to pay by bank transfer instead?" fold for a donation or project gift that is pending or failed (open when mobile money is not available). Never for a paid, pending-with-money-on-the-way or unknown payment: a second way to pay next to "we are waiting for your payment" invites paying twice.
  * **Shop order page** (`/shop/order/{token}`): a second box "Or pay by bank transfer" on every order that is **not cancelled, refunded or completed and whose payment is unpaid, pending or failed** (so also a confirmed order still owing money) and whose own method is not the bank (also right after a gateway start failed): the account with copy buttons, the amount to send (the deposit when the order needs one), the order number to quote and a form to type the transfer reference (`POST /shop/order/{token}/reference` with `via=bank`; `PaymentService::recordCustomerReference($id, $ref, 'bank')` stores a pending customer row with method `bank`; the order keeps its own method; staff then mark it paid under "Paid by" = Bank). The order e-mail carries the account block for pay-on-delivery, booking, mobile-money and Daraza orders (inside `{payment_instructions}`), not for orders paid online. The gate is `PaymentMethods::bankOffer()` (manual switch on and an account number); it does **not** depend on `shop_pay_bank_enabled`, which only adds the "Bank transfer" choice at checkout. **The source is never mixed**: when an account number is typed in Admin > Shop > Payments only the shop fields are used, otherwise only the official account.
* **Not covered**: membership and Academy payment pages (they keep their own settings; the membership fallback reads only the free-text `donate_bank_details` "extra instructions" and `donate_mobile_money`, **not** the official account), the printable shop receipt, the shop checkout page.
* **Tests**: `official-account` (service, card, switch), `donate-thanks` (donate page and pledge flow), `pledge-reminders`, `shop` (which also runs `shop-http`: shop order page, e-mail, `via=bank`), `fund` (project and thank-you pages, proposal e-mail), `pesapal-everywhere` and `daraza-api` (result pages), `payment-options` (switch), `security` (no account in any script), `cloudflare`.

## Pledge reminders (Admin > Donations)

A pledge from the website's pledge form stays "Pledged" until staff mark it received. `Payments\PledgeReminders` e-mails the donor a warm follow-up: it thanks them, says why their gift matters, gives the account details (Settings > Donations: bank transfer, mobile money, PayPal) and the donate link, and asks them to reply with the transaction number.

* **Buttons**: "Remind" on each waiting pledge (needs an amount and a valid e-mail) and "Send thankful reminders to all" above the list (at most 100 per press). Routes `POST admin/donations/{id}/remind` and `POST admin/donations/remind-all` (ability `inbox.manage`).
* **Rules**: the reminder always carries the foundation's official bank account (see "Official bank account" above) with the reference to quote (`DON-<id>`, or `<project name> DON-<id>` for a project pledge), whatever the on-screen switch says, because a person at the foundation sends it; never for a received or cancelled donation; one reminder per pledge per 24 hours (the audit-trail `reminded` event, reference `DON-<id>`, is the memory); each reminder is also written to the activity log.
* **Wording**: built in (`PledgeReminders::DEFAULT`); change it in Settings > Donations > "Pledge reminder e-mail (wording)". Placeholders: `{first_name} {amount} {pledge_date} {payment_details} {donate_link} {site_name}`. Empty = built-in.
* **Test**: `bin/selftest-pledge-reminders.php`.
* **Account details box**: "Your account details" at the top of Admin > Donations (administrators, `settings.manage`) edits the official bank account (account name, number, bank, branch, SWIFT and the note under it), the extra bank instructions, the mobile-money details and the PayPal e-mail (`official_bank_*`, `donate_bank_details`, `donate_mobile_money`, `donate_paypal_email`) without opening Site settings. `POST admin/donations/account-details`. Limits: name, bank and branch up to 80 characters, the number 4 to 40 letters, digits, spaces or dashes, SWIFT 8 or 11 letters and digits, note up to 300. A refused box saves nothing from that form; an empty box, or one equal to the built-in value, goes back to the built-in value (`Settings::reset`); a box missing from the form is left alone. The activity log records which fields changed and only the **last 4 digits** of the number.
