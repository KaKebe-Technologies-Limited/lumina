# Pesapal: card, mobile money and bank payments

Pesapal is the **second way to pay**, offered next to MTN / Airtel mobile money (the first way, which is never replaced) at every place where the site collects money: **donations**, **gifts to a project**, **shop payments**, **membership fees**, and the Academy **sign-up** and **certificate** fees. The payer is taken to Pesapal's own secure page; the site never sees a card number or a PIN. Section 8 explains the switches.

## 1. What you do on Pesapal (business.pesapal.com)

1. Finish **Verify Your Business** (step 1 on your dashboard: registration documents, directors' IDs). Until Pesapal approves it, the transaction limit stays at 0 on the LIVE account. Add your **bank / mobile money settlement details** (the steps after it): Pesapal pays your collected money there.
2. For practice first, create a **sandbox** account at https://developer.pesapal.com (separate from the live account) and copy its **Consumer Key** and **Consumer Secret**. Sandbox money is not real, and sandbox keys only work in sandbox mode.
3. When your business is approved, copy the LIVE **Consumer Key** and **Consumer Secret** from your live Pesapal dashboard (Account > API keys / Developers).

## 2. What you put on the server

The keys go ONLY in the `.env` file next to `index.php` on Hostinger (File Manager > `public_html/.env` > Edit). Never in chat, e-mail or the admin area.

    PESAPAL_ENV=sandbox
    PESAPAL_CONSUMER_KEY=...your key...
    PESAPAL_CONSUMER_SECRET=...your secret...

`PESAPAL_ENV=sandbox` while practising; change it to `PESAPAL_ENV=live` (with the live key and secret) when you want real money. Then open **Admin > Pesapal payments** and press **Test connection**.

## 3. Switch things on

* **Donations**: nothing to switch on. As soon as the keys are in `.env`, the Donate page shows the "Give online" form (name, e-mail, phone, amount, currency). Without keys the form is hidden.
* **Shop**: Admin > Shop > Payments > "Card or mobile money (Pesapal)": turn it on (it shows "On and ready" once the keys exist). You can rename it and edit its text there.

## 4. How a payment works (and why it is safe)

1. The donor / customer fills the form. The site records the donation (still a "pledge") or uses the order, and opens a payment with Pesapal (`SubmitOrderRequest`), then sends the person to Pesapal's page.
2. They pay there. Pesapal sends them back to `/pay/pesapal/return` AND calls our notification address `/pay/pesapal/ipn` (registered automatically on the first payment).
3. Both addresses do the same thing: **ask Pesapal** (`GetTransactionStatus`) what happened. Nothing in the browser address or in the notification is believed. Only a "Completed" answer for the same reference, the same currency and at least the amount asked marks the money as received:
   * a donation becomes **Received**, gets a receipt number (RCT-year-number, the same series as manual receipts) and the team is e-mailed;
   * a shop order becomes **Paid** (and is confirmed automatically if that setting is on).
4. A wrong amount or currency, or a different reference, is kept as **review** for a person to look at and never marked as paid. A failed payment cancels the online donation / reopens the order for "Pay again". A reversal e-mails the team.
5. If the notification or the return never arrives (closed browser), open **Admin > Pesapal payments** and press **Check with Pesapal** on that line.

## 5. Where things are

| What | Where |
| --- | --- |
| Client for the Pesapal API (token, IPN registration, order, status) | `app/services/Pesapal/PesapalClient.php` |
| Ledger + the single place money is decided | `app/services/Pesapal/PesapalPayments.php` (table `pesapal_payments`) |
| Shop "Pay now" | `app/services/Pesapal/PesapalShop.php` |
| Donate form action | `DonateController::online` (POST `/donate/online`) |
| Return page and notification endpoint | `app/controllers/Web/PesapalController.php` |
| Admin page | Admin > Pesapal payments (`app/controllers/Admin/PesapalController.php`) |
| Database | `database/updates/2026-10-pesapal.sql` (also in `schema.sql` and `lumina-live-import.sql`) |
| Tests | `php bin/selftest-pesapal.php` (Pesapal is replaced by a mock: no keys, no internet) |

## 6. Honest limits

* The connection was written from Pesapal's documentation and tested against a mock, never against the real Pesapal (that needs your keys). Do a **sandbox** donation first: give 1,000 UGX, pay with the sandbox test details Pesapal shows, and check the line in Admin > Pesapal payments turns **Paid** and the donation shows **Received**.
* Pesapal's minimum amounts and fees are theirs; the site asks for at least 1,000 UGX online.
* A place can only charge what it has a price for: membership uses the fee set on the member, the Academy fees use the amounts below (0 = free, nothing to pay).

## 7. Academy fees (sign-up fee and certificate fee)

Admin > Pesapal payments > **LAF Academy fees**: type the amount and currency of each fee (0 = free) and **Save the fees**. The same two fees are also in Settings > LAF Academy.

* A fee is only charged when its amount is above 0, Pesapal is connected (keys in `.env`) and the database has the fee columns (`database/updates/2026-10-academy-fees.sql`). Otherwise nobody is blocked and the Academy works as before.
* **Sign-up fee**: a new learner sees the fee on the sign-up page, is sent to Pesapal right after creating the account, and the account cannot sign in until the fee is paid (the waiting page has a "Pay the sign-up fee" button for a second try). After payment the normal approval rules apply. Administrators can waive it on the learner's page ("Waive the fee"), and the learners list shows "Fee unpaid / paid / waived". Accounts that exist already are never charged.
* **Certificate fee**: a certificate earned by passing a final exam is held until the fee is paid (My portal > Certificates > "Pay the certificate fee"). If certificates need approval, the administrator is e-mailed after payment and approves as usual; if not, the certificate becomes available at once. Admin > Academy > Certificates: "Waive fee and approve". Certificates issued by hand by an administrator are free.
* Every payment is listed in Admin > Pesapal payments ("Academy sign-up fee", "Academy certificate fee"). Tests: `php bin/selftest-academy-fees.php`.

## 8. Pesapal everywhere, as the second option

**What payers see** (in the order set in Admin > Payment options: mobile money first, Pesapal second by default):

1. **MTN or Airtel mobile money** (the first option, through the Daraza API) with its banner.
2. **Card, mobile money or bank (Pesapal)** with the line "Pay with Visa or Mastercard, MTN or Airtel Money, or your bank. You are taken to Pesapal's secure page to finish; we never see your card details or PIN."
3. Then the earlier ways (payment link, bank and manual details, pay on delivery).

| Payment point | Pesapal purpose | Switch | Pay address |
| --- | --- | --- | --- |
| Donate page, "Give online" | `donation` | Settings > Donations "How online donations are taken" (`donate_method`; "link" = off) | `POST /donate/online` |
| Fund a project, "Give now" | `project` (a donation row, so receipts and thanks work the same) | `pesapal_place_project` | the "How would you like to give?" list, `pesapal_now` |
| Shop checkout | `shop` | `shop_pay_pesapal_enabled` (also Shop > Payments) | checkout / "Pay now" |
| Membership fee (page after applying, the member's private page) | `membership` | `pesapal_place_membership` | `POST /membership/me/{token}/pay-pesapal` |
| Academy sign-up fee | `academy_signup` | `pesapal_place_academy_signup` | waiting page |
| Academy certificate fee | `academy_cert` | `pesapal_place_academy_cert` | My portal > Certificates |

**Admin > Pesapal payments > "Where Pesapal is offered on the website"**

* **Offer Pesapal everywhere (as the second option)** ticks all six places at once. It needs the ability "Site settings" (`settings.manage`, administrators). It does not touch mobile money, the keys, the other ways or the "online" way, and nothing is charged by pressing it. The shop is ticked only for someone who may change the shop settings.
* Each place shows **Visible to visitors**, or **Ticked, but hidden / Off** with the reasons: keys missing in `.env`; the "online" way is switched off (Payment options); the database update was not imported; the place is switched off; the shop is closed; no fee is set (Academy).
* **"Mobile money only"** (Admin > Payment options) keeps mobile money and switches OFF the online way, links, bank details and pay on delivery, so Pesapal is hidden everywhere. The Pesapal page then shows a warning with a link to Payment options and a button **Switch the online way (Pesapal) back on** (administrators only). It changes only the online way, only when pressed; the other ways stay as they are.
* Defaults on a fresh install: donate, projects, shop and membership are **off** until the button is pressed (nothing is switched on by a deploy: no surprise charges). The two Academy places were always offered before they had a switch, so they stay on; a fee still needs an amount above 0.

**Membership payments.** `PesapalPayments::begin('membership', memberId, ...)` -> the member pays on Pesapal -> return page / IPN call `settle()` -> only a "Completed" answer from Pesapal for the same reference and currency and at least the amount asked records a **received member payment** (method `pesapal`, our reference, a receipt number, the thank-you e-mail, the card trigger), exactly like the Daraza and manual payments. It is idempotent (the ledger row is claimed once and a payment with the same reference is never added twice); a wrong amount, currency or reference goes to **review**, a failed payment records nothing. The audit trail (Admin > Payments > Audit trail) and the activity log (`membership.pesapal`) keep the steps. The member is sent back to their private page with a message. The manual and Daraza ways are unchanged.

**Database.** `database/updates/2026-11-pesapal-everywhere.sql` (also in `schema.sql`, `seed.sql` and `lumina-live-import.sql`): `pesapal_payments.purpose` also takes `project` and `membership`; the four `pesapal_place_*` settings (`INSERT IGNORE`, nothing already saved changes); the shop's Pesapal label becomes "Card, mobile money or bank (Pesapal)" only where it still has the old default. Import it in phpMyAdmin; until then the new places stay hidden ("the database update has not been imported").

**Tests.** `php bin/selftest-pesapal-everywhere.php` (also run by `bin/selftest.php`): the switch, visibility and reasons, the order from Payment options, "Mobile money only" and its warning, membership and project payments with a mock Pesapal (idempotent, wrong amount, reference, currency, failed), forged IPN refused, and the abilities.
