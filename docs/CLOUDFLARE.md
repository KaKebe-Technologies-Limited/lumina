# Switching Cloudflare on - step by step for the owner

**Who this is for:** the owner of the Lumina Africa Foundation website. You do not need to be technical. Follow the steps in order, one at a time, and tick them off on **Admin > Security > Cloudflare** as you go.

**How long:** about one hour of clicking, then 1 to 24 hours of waiting while the internet learns the change. Do it on a quiet day, not during a campaign, an exam or a big payment day.

> **About the names of buttons.** Everything written in **bold** that belongs to the Cloudflare or Hostinger screens is the name of a menu or button as we know it today (November 2026). **Cloudflare and Hostinger change their menus from time to time, so every label in this guide may differ slightly** from what you see. If a name is not exactly there, look for the closest one with the same meaning, or use the search box at the top of the Cloudflare dashboard.

**Never give anyone your Cloudflare password, a Cloudflare API token or a verification code. Not to a helper, not to Claude, not in a chat.** If a developer helps, sit next to them and type the password yourself.

---

## 1. What Cloudflare does for the site, and what it cannot do

Cloudflare is a free shield that stands between visitors and the hosting. Every visit goes to Cloudflare first, which throws away a lot of bad traffic (attack tools, bots, floods) and passes the rest on to Hostinger.

**Honest limits.** No tool can *eradicate* every security threat, and nobody can promise that. Cloudflare reduces the risk; it does not remove it. A weak password, a stolen phone or a mistake by a person still works around any shield. Protection comes in layers, and Cloudflare is one of them:

| Layer | What it stops | Where it lives |
|---|---|---|
| Cloudflare firewall (WAF) and bot filter | common attacks, scanning robots, floods of requests | Cloudflare (this guide) |
| Rate limits | someone guessing passwords or flooding a form | Cloudflare (section 8) and the website itself (every public form and sign-in) |
| Optional Turnstile bot check | robots filling in forms | Cloudflare + website (section 10) |
| HTTPS everywhere and HSTS | people reading or changing what travels between a visitor and the site | Cloudflare + website |
| Two-step sign-in by e-mail, screen lock, sign-in limits | a stolen or guessed admin password | the website (Admin > Security) |
| Hidden private files | `.env`, database and source files are refused at the web server | the website's `.htaccess` files |
| Backups | the damage if something still goes wrong | Hostinger + `docs/BACKUPS.md` |

Nothing in the website changes for visitors until you switch Cloudflare on. All the new code does nothing while Cloudflare is absent.

## 2. Before you start

You need:

* Access to the **Hostinger hPanel** (where the domain and the e-mail are managed).
* A free **Cloudflare account** (an e-mail address and a strong password kept in a password manager).
* Access to the hosting **File Manager** (to add one line to the `.env` file in section 6).
* Admin access to the website (Admin > Security).

Write down, before touching anything: how e-mail for `@luminaafricafoundation.com` works today (Hostinger mailboxes? Google? something else?). **Breaking the mail records stops e-mail**, so section 3 is the careful part.

## 3. Create the account, add the site, copy the DNS records

*Labels in bold may differ slightly: Cloudflare changes its menus.*

1. Go to the Cloudflare website, choose **Sign up**, make the account, confirm the e-mail they send you. Turn on two-step sign-in for the Cloudflare account itself (**My Profile > Authentication**).
2. In the dashboard choose **Add a domain** (also called **Onboard a domain**). Type `luminaafricafoundation.com` (no `www`, no `https://`).
3. Choose the **Free** plan.
4. Cloudflare now **scans and imports the DNS records** it can find. This is a convenience, not a guarantee. **Do not trust the scan.**
5. **Copy ALL the existing records from Hostinger first.** In another browser tab open hPanel > **Domains** > your domain > **DNS / Nameservers** > **DNS records**. Compare the list in Hostinger with the list in Cloudflare (**DNS > Records**), line by line:
   * Every **A** and **AAAA** record (the website address).
   * Every **CNAME** record (for example `www`, `autodiscover`, `autoconfig`, and any Hostinger e-mail ones).
   * Every **MX** record (where e-mail is delivered).
   * Every **TXT** record (SPF, DKIM, DMARC, Google or other verification codes).
   * Any **SRV**, **CAA** or other record Hostinger has.
   Anything missing in Cloudflare: **Add record** and type it exactly. A photograph or screenshot of the Hostinger list is a good safety copy.
6. **Set the cloud colour of each record:**
   * `luminaafricafoundation.com` (the root) and `www`, type A / AAAA / CNAME: **orange cloud = Proxied**. These are the only ones that should be orange, because they are the website.
   * **Everything for e-mail: grey cloud = DNS only.** That means every **MX** and **TXT** record (these cannot be proxied anyway), and every **A** or **CNAME** record that e-mail uses, such as `mail`, `smtp`, `imap`, `pop`, `webmail`, `autodiscover`, `autoconfig`.
   * Also **grey** for `ftp`, `cpanel`, `ssh` or any record that is not the website.
   > **Warning.** A proxied (orange) mail host, or a missing MX / SPF / DKIM / DMARC record, silently stops or spoils e-mail. After the switch, send a test e-mail to your own address and one from it.
7. Press **Continue**. Cloudflare shows two **name servers** (for example `something.ns.cloudflare.com`). Keep this page open: you need those two names in section 4.

## 4. Change the name servers at Hostinger

*Labels may differ slightly.*

Today the domain uses the Hostinger name servers `ns1.dns-parking.com` and `ns2.dns-parking.com`. Cloudflare only starts protecting the site once these are replaced by the two Cloudflare names.

1. In hPanel open **Domains**, choose `luminaafricafoundation.com` and open **DNS / Nameservers** (sometimes **Manage > Nameservers**).
2. Choose **Change nameservers** (or **Use custom nameservers**).
3. Replace the two existing name servers with the **two names Cloudflare gave you**, exactly as shown. Delete any third or fourth one. Save.
   *If the domain was bought at another company (a registrar), do this in that company's account instead.*
4. Go back to Cloudflare and press **Done, check nameservers** (or **Check nameservers now**).
5. **Wait.** Usually 5 minutes to 2 hours; it can take up to 24 hours, rarely 48. Cloudflare sends an e-mail when the site is **Active**. During the wait the site and e-mail keep working as before.

## 5. HTTPS and encryption settings

*Labels may differ slightly. Do this once the site shows **Active**. All these are under **SSL/TLS** in the left menu.*

1. **Overview > encryption mode: Full (strict).**
   **Never choose Flexible.** With Flexible, Cloudflare talks to Hostinger over plain http, Hostinger answers "go to https", and visitors get stuck in an endless redirect ("too many redirects"). The website needs Full (strict).
   *Full (strict)* means the hosting must have a real certificate for the domain. Hostinger gives a free SSL certificate for the domain (hPanel > **SSL**): make sure it is installed and shows **Active**. A Cloudflare *Origin CA certificate* is another option, but Hostinger shared hosting usually lets you install only its own certificate, so use Hostinger's free one. If the page ever shows error 526 ("invalid SSL certificate"), the Hostinger certificate has expired: renew it in hPanel > SSL. As an emergency measure only, switch to **Full** (not strict) for a day.
2. **Edge Certificates > Always Use HTTPS: On.**
3. **Edge Certificates > Automatic HTTPS Rewrites: On.**
4. **Edge Certificates > Minimum TLS Version: TLS 1.2.**
5. **Edge Certificates > TLS 1.3: On** (normally already on).
6. **HSTS** (**Edge Certificates > HTTP Strict Transport Security**): **leave it OFF in Cloudflare.** The website itself already sends the correct HSTS header (180 days, only when the page really arrived over HTTPS), and two switches doing the same job can disagree. If you ever turn it on in Cloudflare, use a short time first (1 month) and **do NOT tick "Preload"**: preload puts the domain on a list built into browsers that is slow and hard to undo, and *includeSubDomains* would then force every subdomain (mail, webmail, test sites) to HTTPS too.
7. Do not enable the **Always Online** or **Cloudflare Zaraz** features.

## 6. Tell the website that Cloudflare is in front

The website must know who the *real* visitor is. Without this, every visitor looks like a Cloudflare address, and one person's mistakes (wrong passwords, a flood) would be counted against everybody.

1. Open the hosting **File Manager** and edit the `.env` file next to `index.php` (hidden files must be shown).
2. Add this line (a new line at the end is fine):
   ```
   CLOUDFLARE_PROXY=1
   ```
3. Save. Nothing else to restart.

**How it stays safe.** The website believes Cloudflare's "this is the visitor" header *only* when the connection really comes from a Cloudflare address (Cloudflare publishes the list; it is built in, dated 2026-11-26). A person who contacts the hosting directly and invents that header is ignored. If Hostinger's own front server sits between Cloudflare and the site, add its address range to `TRUSTED_PROXIES` as before; both work together.

4. Open **Admin > Security > Cloudflare** (the card). It should say **This request arrived through Cloudflare** and show your real address as the visitor. Tick the boxes you have completed.

## 7. Caching: Cloudflare must never keep private pages

By default Cloudflare only keeps pictures, stylesheets and scripts, not web pages. The website also tells every shared cache "never store this" on private areas (admin, learner portal, membership, shop, donate, payments, API). The rule below is a **second safety belt**.

*Labels may differ slightly.* **Caching > Cache Rules > Create rule** (older accounts: **Rules > Page Rules**).

* **Rule name:** `Never cache private areas`
* **If incoming requests match... > Edit expression**, paste:

```
(starts_with(http.request.uri.path, "/admin")) or
(starts_with(http.request.uri.path, "/academy")) or
(starts_with(http.request.uri.path, "/membership")) or
(starts_with(http.request.uri.path, "/shop")) or
(starts_with(http.request.uri.path, "/donate")) or
(starts_with(http.request.uri.path, "/pay")) or
(starts_with(http.request.uri.path, "/api")) or
(starts_with(http.request.uri.path, "/forms")) or
(starts_with(http.request.uri.path, "/fund-a-project")) or
(starts_with(http.request.uri.path, "/track-application")) or
(starts_with(http.request.uri.path, "/ambassador")) or
(starts_with(http.request.uri.path, "/privacy/request")) or
(starts_with(http.request.uri.path, "/install")) or
(starts_with(http.request.uri.path, "/bono")) or
(http.cookie contains "lumina_session")
```

* **Then: Cache eligibility > Bypass cache.** **Deploy.**

The last line means: anyone who has a session cookie (signed in, or with a form open) is never served from the cache.

**Do NOT** create a "Cache Everything" rule, and do not turn on **Tiered Cache** or **Cache Reserve**. Leave **Browser Cache TTL** on **Respect existing headers**.

Check it later: in section 15, or run the check on Admin > Security > Cloudflare ("Check the cache headers now"). Every private page must say `no-store`.

## 8. Firewall, bots and rate limits

*Labels may differ slightly.* Under **Security** in the left menu.

1. **Security > Settings (or WAF) > Security level: Medium.** (High only during an attack; see section 13.)
2. **Browser Integrity Check: On.**
3. **WAF > Managed rules:** turn on the **Cloudflare Managed Ruleset** (the free plan includes a basic one). Leave it on its default actions.
4. **Security > Bots > Bot Fight Mode: On.**
   > **Payments note.** Bot Fight Mode cannot be excused for single addresses on the free plan, and it can occasionally challenge the *automatic messages* that Pesapal and Flutterwave send to the website to say "this payment succeeded" (`/pay/pesapal/ipn`, `/shop/webhook/flutterwave`). After switching it on, **make a small test payment** (Admin > Payments shows whether it was confirmed automatically). If payments stay "pending" when they should be paid, switch Bot Fight Mode **off** again; the other layers still protect you. The same caution applies to the "I'm Under Attack" mode.
5. **A rule that lets payment messages through the custom rules** (recommended, free): **Security > WAF > Custom rules > Create rule**, name `Payment notifications`, expression:
   ```
   (http.request.uri.path eq "/pay/pesapal/ipn") or (starts_with(http.request.uri.path, "/shop/webhook/"))
   ```
   Action **Skip**, tick all the remaining custom rules and rate limiting rules, so none of your later rules ever blocks them. Put this rule **first**.
6. **Rate limiting rule** (**Security > WAF > Rate limiting rules > Create rule**). The free plan allows **one** rule, so it covers the sign-in and form addresses together. Name `Sign-in and forms`, expression (uses the real paths of the website):
   ```
   (http.request.method eq "POST" and (
     http.request.uri.path in {"/admin/login" "/admin/verify" "/admin/forgot-password" "/academy/login" "/academy/register" "/academy/forgot" "/academy/verify/code" "/academy/verify/resend" "/contact" "/subscribe" "/privacy/request" "/membership/register" "/involvement" "/involvement/ambassadors" "/careers/apply" "/donate/pledge" "/donate/mobile-money" "/donate/online" "/track-application" "/academy/track" "/bono/ask"
   } or ends_with(http.request.uri.path, "/apply") or starts_with(http.request.uri.path, "/forms/")))
   ```
   **Characteristics:** IP. **Rate:** 20 requests per 1 minute (or 10 seconds, whatever the free plan offers). **Action:** Managed Challenge (or Block for 10 minutes). The website has its own, stricter limits too (for example 5 sign-in failures per 15 minutes); this one stops floods *before* they reach Hostinger.
7. **Security > Settings > Challenge Passage:** 30 minutes (default).
8. Under **Security > Events** you can later see what Cloudflare blocked.

## 9. Optional: protect the admin even more

Two choices. Do neither, one or both. The admin already has two-step e-mail sign-in, a screen lock and sign-in limits.

**A. Ask for a challenge when the admin is opened from outside Uganda.** *WAF > Custom rules > Create rule*, name `Admin only from Uganda`, expression:
```
(starts_with(http.request.uri.path, "/admin") and ip.src.country ne "UG")
```
Action: **Managed Challenge**. Careful: staff who travel, or use a mobile network that routes through another country, will see a challenge (a passed challenge lets them in; use **Block** only if nobody ever works from abroad). Do not use Block on a Monday you are on holiday.

**B. Cloudflare Access (Zero Trust).** Free for up to 50 people. Cloudflare asks for an e-mail code (or a Google sign-in) *before* the admin page even loads, so strangers never see the sign-in form. Search the dashboard for **Zero Trust > Access > Applications > Add an application > Self-hosted**, application domain `luminaafricafoundation.com`, path `admin`, policy: **Allow** for the e-mail addresses of your staff. It is a second sign-in on top of the website's own, so staff type two codes. Make sure `/admin` is the *only* path covered, so payment messages and visitors are not affected.

## 10. Optional: Cloudflare Turnstile on the public forms

Turnstile is Cloudflare's friendly alternative to the "pick the traffic lights" puzzle. It usually runs invisibly and only bothers suspicious visitors. It is **off** until you add its two keys, and the website's own honeypot trap and limits stay in place either way.

1. In the Cloudflare dashboard open **Turnstile > Add site** (label may differ). Name it `Lumina website`, add the domain `luminaafricafoundation.com`, widget mode **Managed**. Cloudflare shows a **Site key** and a **Secret key**.
2. In the hosting File Manager add to `.env`:
   ```
   TURNSTILE_SITE_KEY=the-site-key
   TURNSTILE_SECRET_KEY=the-secret-key
   ```
   The secret stays in this file only. The website never shows it, logs it or sends it to a browser. (Optional: `TURNSTILE_FAIL=closed` refuses forms when Cloudflare's checking service cannot be reached; the default `open` keeps forms working and records the problem in the log. `TURNSTILE_ENABLED=0` switches it off without deleting the keys.)
3. Open the sign-in page, the contact page and the newsletter box and try them once. Admin > Security > Cloudflare shows "Turnstile: ON".

It protects: admin sign-in, learner sign-in and sign-up, membership application, contact, newsletter, donate (all three forms), volunteer, ambassador and job applications, and the data-rights request. The browser is allowed to talk to `challenges.cloudflare.com` only on the pages that carry the widget.

## 11. Features to leave OFF (they change the pages and can break things)

*Labels may differ slightly.*

* **Speed > Optimization > Rocket Loader: Off.** It delays and rewrites scripts; the site's sign-in, forms and exam timer rely on their scripts loading in order.
* **Auto Minify (JavaScript, CSS, HTML): Off** (it may no longer exist in your account; that is fine). The site is already minified.
* **Email Address Obfuscation: Off** (**Scrape Shield**). When on, Cloudflare hides e-mail addresses on pages; it can break `mailto:` links and the contact details in the footer. If you keep it on, check every page that shows an e-mail address.
* **Hotlink Protection: Off** (it can break pictures in e-mails and shared links). **Server-side Excludes: leave default.**
* **Web Analytics / the automatic Cloudflare beacon: Off.** It adds a script from `cloudflareinsights.com`, and the website's Content-Security-Policy does *not* allow it, so the browser would refuse it and show a harmless error. (If you ever want it: say so to the developer so the policy is extended on purpose.)
* **Mirage, Polish, Rocket Loader, Early Hints, 0-RTT:** leave off or default.
* **Always Online:** off (it serves an old copy of pages).

## 12. Optional: allow only Cloudflare to reach the hosting ("origin lock")

Right now someone who knows the hosting address could call it directly and skip Cloudflare. The root `.htaccess` contains a ready-made block (section 4 at the bottom of the file) that refuses everybody except Cloudflare (and the server itself). It is **commented out (off) by default**.

> **WARNING.** Switching this on **before** Cloudflare is live and working takes the **whole website offline** for everyone ("403 Forbidden"). Switch it on only when ALL are true:
> 1. Cloudflare shows the site as **Active** and it has worked for a few days;
> 2. Admin > Security > Cloudflare says "arrived through Cloudflare" **and** the "connection from" address in the same card is a Cloudflare address (it begins like `104.`, `172.64.` to `172.71.`, `162.158.` or `2606:4700`). **If the connection comes from some other address, Hostinger has its own front server between Cloudflare and the site, and the lock must stay OFF** because it would block that server too. We cannot see Hostinger's set-up from here, so this check on the live site decides;
> 3. You can open the hosting File Manager to undo it (put the `#` signs back).

To switch on: in `.htaccess`, remove the `# ` at the start of the lines between `BEGIN origin lock` and `END origin lock`, save, and reload the site at once. If it shows "403", re-add the `#` signs immediately.

**Authenticated Origin Pulls** (Cloudflare proves itself with a certificate) is the stronger method, but it needs a server setting that Hostinger shared hosting does not offer, so the address list is the practical option. Cloudflare changes its address list rarely. Once a year compare https://www.cloudflare.com/ips with the block in `.htaccess` and `app/core/Cloudflare.php` (a developer task; `CLOUDFLARE_EXTRA_RANGES` in `.env` adds ranges quickly).

Whatever you decide here, the web server already refuses to hand out `.env`, `.git`, `app/`, `bin/`, `database/`, `docs/`, `scss/`, `storage/`, `tools/`, `vendor/`, backups (`.sql`, `.zip`, `.gz`, `.bak` and similar) and key files; the test `bin/selftest-cloudflare.php` requests about 40 such addresses and expects "403 forbidden" or "404 not found".

## 13. "I'm Under Attack" mode and notifications

**If the site is being flooded** (very slow, or Hostinger warns you): in Cloudflare open **Security > Settings > Security Level** and choose **I'm Under Attack!** (or the **Under Attack Mode** quick button on the overview). Every visitor then sees a short "checking your browser" page for about five seconds. **Turn it off again (back to Medium) when it is over.** While it is on, payment messages from Pesapal and Flutterwave can be blocked (see section 8), so check pending payments afterwards.

**Notifications.** In **Notifications** (account level) choose **Add** and pick the alerts your plan offers: *HTTP DDoS attack alert*, *Security events / WAF alert*, *Origin server errors / health*, and *SSL certificate expiry*. Send them to a mailbox that someone reads.

## 14. Privacy: tell visitors that Cloudflare handles their visit

Cloudflare sees every visitor's IP address and the pages they open, so it is a data processor. **Suggestion for whoever maintains the privacy page** (the data-protection work, not changed here):

> "Our website is protected by Cloudflare, Inc., which filters malicious traffic. To do this, Cloudflare processes your IP address and technical details of your visit. Cloudflare may process this data on servers outside Uganda. Our legal basis is our legitimate interest in keeping the website secure."

Also: list Cloudflare in the data-processor annex (Annex G) and accept Cloudflare's data-processing addendum in the Cloudflare account. `docs/DATA-INVENTORY.md` has a row for it (row 22). Tick the box on the Security card when done.

## 15. Check that it works

1. **Admin > Security > Cloudflare** (reload the page): it says *arrived through Cloudflare*, the visitor address is your own address (not a `104.` or `172.` one), the page is HTTPS, and Turnstile shows what you expect.
2. Press **Check the cache headers now**. Every private page (admin, academy, membership, donate, shop) must be green ("Never stored"). Wait ten minutes and press it again; none may say "Cloudflare served this from its cache".
3. In a private/incognito window open `https://luminaafricafoundation.com` and `https://www.luminaafricafoundation.com`: both must open normally, with the padlock, and without "too many redirects".
4. Sign in to the admin (the e-mailed code must still arrive) and the learner portal.
5. Make a very small test donation or shop payment and check that it is confirmed.
6. Send an e-mail to your address and reply to it.
7. A developer can also run `php bin/check-cloudflare.php https://www.luminaafricafoundation.com` for a table of the same cache check.

## 16. If something goes wrong: how to go back

* **Quick (seconds):** in Cloudflare **Overview > (three dots) Advanced actions > Pause Cloudflare on site** (label may differ). Traffic goes straight to Hostinger.
* **Per record:** **DNS > Records**: click the orange cloud so it turns **grey** (DNS only) for `luminaafricafoundation.com` and `www`.
* **Full exit:** in Hostinger hPanel > Domains > your domain > Nameservers, set the name servers back to `ns1.dns-parking.com` and `ns2.dns-parking.com` (the Hostinger ones). Takes the same 1 to 24 hours.
* If the origin lock (section 12) is on, **switch it off first** by putting the `#` signs back in `.htaccess`.
* `CLOUDFLARE_PROXY=1` can stay in `.env` (it does nothing when Cloudflare is absent) or be removed.
* "Too many redirects" = the SSL mode is Flexible. Change it to **Full (strict)**.
* "Error 521/522/523" = Hostinger is not answering Cloudflare (hosting problem or the origin lock is on).
* "Error 526" = the Hostinger certificate is invalid or expired: renew it in hPanel > SSL.
* E-mail stopped: some mail record is orange or missing; compare with the Hostinger list from section 3.

## 17. For the developer

* **Real visitor address:** `Request::ip()` / `Request::viaCloudflare()` (`app/core/Request.php`) use `App\Core\Cloudflare::trustedList()` (TRUSTED_PROXIES plus, only with `CLOUDFLARE_PROXY=1`, Cloudflare's ranges). The chain (REMOTE_ADDR, then X-Forwarded-For from the right) is walked; reaching a Cloudflare address inside the trusted part makes `CF-Connecting-IP` authoritative. Rate limits, the audit log, the security log, e-mail codes, forms and the visitor log all call `$request->ip()` (no other code reads `REMOTE_ADDR`). HTTPS behind a proxy is recognised from `X-Forwarded-Proto` or `CF-Visitor`, only from a trusted peer; HSTS is sent only then.
* **Ranges:** `Cloudflare::IPV4 / IPV6`, copied from cloudflare.com/ips-v4 and ips-v6 on 2026-11-26. `CLOUDFLARE_EXTRA_RANGES` adds more. Keep the block in `.htaccess` in step with them.
* **Cache safety:** `Response::securityHeaders()` sends `private, no-store` for every HTML page below `Cloudflare::NO_STORE_PREFIXES` and for temporary redirects; JSON and downloads already did. Deliberately public data (newsletter API, calendar feed, sitemap, social images) keeps its own headers and is excluded from the "private" judgement; the Cloudflare bypass rule still covers `/api`.
* **Turnstile:** `App\Services\Security\Turnstile` (verify helper with an injectable transport, fixed HTTPS host, TLS verified, no redirects, 5 second timeout), `turnstile_widget()`, the `turnstile` route middleware (after `honeypot`). CSP gains `https://challenges.cloudflare.com` in script-src, frame-src and connect-src only on a response whose page rendered the widget. The secret is read from the environment at the moment of the check and never stored in config.
* **Admin card:** `CloudflareStatus` (ticks in settings `security_cf_steps`, last cache check in `security_cf_cache`), routes `POST admin/security/cloudflare-steps|cloudflare-check`, ability `settings.manage`.
* **Tests:** `php bin/selftest-cloudflare.php` (also part of `bin/selftest.php`).
* **Not verified from here:** how Hostinger's front server (hcdn) behaves once Cloudflare is in front. The code handles Cloudflare alone and Cloudflare followed by a trusted proxy; the card tells the real story on the live site.

## 18. What stays your job

Keep the admin passwords long and private, keep two-step sign-in on, keep Hostinger and PHP updated, keep backups and test a restore once a year (`docs/BACKUPS.md`), and look at Admin > Security once a week. Cloudflare is a shield, not a promise.
