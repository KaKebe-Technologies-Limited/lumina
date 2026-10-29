# Pesapal: the LAF Academy certificate fee

A learner who finished a course **pays through Pesapal (MTN / Airtel mobile money or a card) before the certificate is requested or issued**. The Pesapal code was ported from
the ObiFunds / ChamaFunds scripts (`includes/pesapal_functions.php`, `pesapal_config.php`, `pesapal_ipn.php`): the same API v3 calls and the same merchant account, with the
checks those sites lack added (amount, currency and reference are compared, duplicates are flagged, the return address is signed).

## How a payment goes

```
finish the last lesson / pass the exam
  -> "Pay for your certificate"            GET  /academy/courses/{slug}/checkout
  -> [Pay UGX 20,000 with Pesapal]         POST /academy/courses/{slug}/checkout   (amount read from OUR settings, never from the browser)
  -> Pesapal hosted page (mobile money / card)
  -> back to the site                       GET  /academy/pay/return/{key}?OrderTrackingId=..&OrderMerchantReference=..
  -> and, separately, Pesapal's notification GET  /academy/pay/ipn?OrderTrackingId=..&OrderMerchantReference=..
  -> the site ASKS PESAPAL (GetTransactionStatus) and only then marks the payment paid
  -> the certificate request is filed (waits for the Academy team, or is issued at once when "certificates must be approved" is off) and a receipt is e-mailed
```

* Code: `app/services/Payments/PesapalClient.php` (the API), `app/services/Academy/CertificatePayments.php` (fee rules, start, settle), `app/controllers/Web/AcademyPaymentController.php`
  (learner pages), `app/controllers/Admin/AcademyPaymentsController.php` (Admin > LAF Academy > **Payments**). Table `academy_payments`, column `academy_courses.certificate_fee`.
* The gate: `CertificateService::request()` and `CourseService::checkCompletion()` both ask `CertificatePayments::required()` first, so there is no way to a certificate that skips the
  fee (not through the approval setting being off, not through the "Request my certificate" button). **Only an administrator** can bypass it: *Issue a certificate by hand* on the learner page,
  or **Payments > Let a learner have a certificate without paying** (recorded as a `waived` payment with the reason).
* Challenge certificates (winners / participants) are not charged.

## Set it up

1. **Keys** in the `.env` file (never in the database, never in git). The keys of the ObiFunds / ChamaFunds merchant account are the same pair and work here:
   ```
   PESAPAL_ENV=live
   PESAPAL_CONSUMER_KEY=...
   PESAPAL_CONSUMER_SECRET=...
   ```
   Leave `PESAPAL_IPN_ID` empty. Pesapal needs ONE notification address per site: on the first payment the site registers `https://YOUR-SITE/academy/pay/ipn` with Pesapal and remembers the id
   (a different site, domain or folder gets its own registration). The id that ObiFunds uses belongs to obifunds.com and must **not** be copied.
2. **Production only:** `APP_URL=https://your-domain` must be set (README, "Deploy"): the return and notification addresses are built from it. Pesapal rejects a non-public address.
3. **Database:** `php bin/setup.php` (or import `database/updates/2026-10-academy-certificate-payments.sql` in phpMyAdmin). Until the table exists the site keeps issuing certificates without a payment.
4. **Fee:** Settings > LAF Academy > **Certificate fee (UGX)** (shipped at **20,000: change it to your price**; 0 = free). One course can have its own fee (Admin > Academy > Courses > edit; empty = the site-wide fee).
5. **Check:** Admin > LAF Academy > Payments > **Test the connection** (asks Pesapal for a token; shows nothing secret).
6. **Cron (recommended):** `*/15 * * * * php /path/to/lumina/bin/academy-payments-recheck.php`: asks Pesapal about payments still waiting (a learner closed the page and the notification did not arrive)
   and closes payments Pesapal never confirmed after two days.

## What is checked before money counts

`CertificatePayments::settle()` marks a payment **paid** only when Pesapal's own answer says *Completed* **and**:

| Check | If it fails |
|---|---|
| the order reference Pesapal reports equals the one we sent | `review` |
| the currency equals the one we asked for | `review` |
| the amount received is at least the fee stored on the row | `review` (more than the fee is fine) |
| this learner has not already paid for this course | `review` ("duplicate: refund it in Pesapal or keep it as a credit") |

A payment in **review** never unlocks the certificate by itself: an administrator opens Payments and presses **Accept** (or refunds it in Pesapal). The Payments menu shows a yellow number for them.

Other safeguards: the browser never supplies the amount, status, course or tracking id (the tracking id is the one Pesapal gave us, kept on the row); the return address carries an HMAC key of the
reference that only this server can compute (an old link, a guess or a bot gets the same neutral page for every reference); the notification is authenticated by asking Pesapal back, is idempotent,
ignores unknown references, answers HTTP 500 only when Pesapal could not be asked (so Pesapal retries); the API host is fixed (`pay.pesapal.com`), TLS is verified, redirects are not followed, answers are
size-capped and the keys are never logged or shown; the learner is sent only to an https address on `pesapal.com`; payment starts are rate-limited; **no card or mobile-money number is ever stored or e-mailed**.
If the keys are missing the certificate stays locked (it fails closed) and the checkout page says online payment is not available.

## Testing

* `php bin/selftest.php` includes the suite `bin/selftest-academy-payments.php` (also runnable alone). It starts the local mock `bin/dev/mock-pesapal.php`, never contacts the real Pesapal and never touches the real keys.
* To click through a payment by hand without spending money: `php -S 127.0.0.1:8098 bin/dev/mock-pesapal.php`, then in `.env` (honoured only while `APP_ENV=local`):
  `PESAPAL_DEV_BASE_URL=http://127.0.0.1:8098`, and tell the mock which keys to accept: `curl -X POST http://127.0.0.1:8098/__mock/config -d '{"key":"<your key>","secret":"<your secret>"}'`
  (or use `mock-key` / `mock-secret` in `.env`). The mock's page has *Pay*, *Decline* and *Leave without paying* links.
* **A real payment must be tried once on the live site** with a small amount (the mock proves our code and our reading of the contract, not Pesapal itself).
  Locally Pesapal cannot call `localhost`: the payment is still confirmed when the learner returns, but for the background notification use a tunnel (`PESAPAL_PUBLIC_URL=https://xxxx.ngrok-free.app`).
* `PESAPAL_ENV=sandbox` needs sandbox keys, and PHP's cURL must trust the sandbox certificate (on XAMPP/Windows set `curl.cainfo` in php.ini to an up-to-date `cacert.pem`).
