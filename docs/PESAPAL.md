# Pesapal: card, mobile money and bank payments

Pesapal takes **donations** (Donate page > "Give online") and **shop payments** (checkout > "Card or mobile money (Pesapal)") through Pesapal's own secure page. The site never sees a card number or a PIN.

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
* Memberships and Academy fees do not use Pesapal yet (they have no price in the system); the same `PesapalPayments::begin()` call can be used for them later.

## 7. Academy fees (sign-up fee and certificate fee)

Admin > Pesapal payments > **LAF Academy fees**: type the amount and currency of each fee (0 = free) and **Save the fees**. The same two fees are also in Settings > LAF Academy.

* A fee is only charged when its amount is above 0, Pesapal is connected (keys in `.env`) and the database has the fee columns (`database/updates/2026-10-academy-fees.sql`). Otherwise nobody is blocked and the Academy works as before.
* **Sign-up fee**: a new learner sees the fee on the sign-up page, is sent to Pesapal right after creating the account, and the account cannot sign in until the fee is paid (the waiting page has a "Pay the sign-up fee" button for a second try). After payment the normal approval rules apply. Administrators can waive it on the learner's page ("Waive the fee"), and the learners list shows "Fee unpaid / paid / waived". Accounts that exist already are never charged.
* **Certificate fee**: a certificate earned by passing a final exam is held until the fee is paid (My portal > Certificates > "Pay the certificate fee"). If certificates need approval, the administrator is e-mailed after payment and approves as usual; if not, the certificate becomes available at once. Admin > Academy > Certificates: "Waive fee and approve". Certificates issued by hand by an administrator are free.
* Every payment is listed in Admin > Pesapal payments ("Academy sign-up fee", "Academy certificate fee"). Tests: `php bin/selftest-academy-fees.php`.
