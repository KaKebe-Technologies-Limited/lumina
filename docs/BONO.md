# Bono AI: the support assistant

A chat window ("Ask Bono AI", bottom-left of every public page) that answers visitors' questions from the website itself. It reads the whole website every day, can be **taught** answers by an administrator, keeps the questions it could not answer so the team can teach them, and can optionally use Claude to phrase its answers.

## How it works

1. **Learning** (`App\Services\Bono\BonoIndexer`): reads every public page listed in the sitemap exactly as a visitor sees it (menus, footer, forms, scripts and hidden text are left out; pages marked `noindex` and private areas such as `/admin`, the Academy dashboard, certificates, shop checkout and membership pages are never read), cuts the text into passages under their headings and stores them with word stems. The organisation's contact details and the Academy rules (joining, approval time, certificates) are learned straight from the settings. Unchanged pages cost nothing, changed pages are learned again, and pages that left the sitemap are forgotten. A new page, news item or course is therefore known the next night.
2. **Answering** (`BonoEngine`): small talk, then an answer an administrator **taught** for the same question (always wins), then the best passages (BM25 ranking with word forms and synonyms such as office/address, boosts for a page named after the question) which are turned into an answer with links to the pages. If the website does not cover the question Bono says so, gives the WhatsApp line and the contact page, and keeps the question.
3. **Claude, optional** (`BonoAi`): with `ANTHROPIC_API_KEY` in `.env`, the matching passages and the question are sent to the Claude Messages API, which writes a short friendly answer using **only** those passages. Without a key, or if the call fails, is refused or times out, Bono answers from the passages itself, so it always works. Nothing is sent to Claude when the website has nothing about the question, and no personal data is ever sent (only the question text, the last few chat lines and the website passages). The website text is passed as data inside `<context>` and the system prompt tells the model never to follow instructions found in it. Defaults: model `claude-opus-5-5` (`BONO_MODEL` in `.env` to change it; `claude-haiku-5-5` is the cheapest), low effort, the API's server-side refusal fallback on the models that support it. The call is a plain HTTPS request (this site has no Composer dependencies).

## Set up

* Import `database/lumina-live-import.sql` (adds the four `bono_*` tables; safe to repeat), upload the files.
* **Admin > Bono AI > Learn the website now** (about a minute) for the first time.
* **Cron job, every night** (Hostinger > Advanced > Cron Jobs): `30 2 * * * /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/bono-learn.php > /dev/null 2>&1`. `--force` re-reads every page, `--verbose` prints the counts. A run has a time budget; the pages not reached are taken first next time.
* Optional: `ANTHROPIC_API_KEY=sk-ant-...` (console.anthropic.com > API keys) and `BONO_MODEL=...` in the live `.env`. Settings > Bono AI has the on/off switches (window, Claude) and the opening message.
* On a copy where the public address differs from where the site can be reached (a local sub-folder), `BONO_BASE_URL=http://localhost/lumina` tells the learner where to read the pages.

## Teaching it ("learning every day")

Admin > Bono AI lists **the questions Bono could not answer** (how often, last asked). **Teach the answer** writes a question and its answer; from then on Bono gives that answer for the same question. Taught answers can be switched off or removed, and the page shows how often they are used, how many visitors said an answer helped, and the latest questions. Questions are stored as plain text without name, address or IP and deleted after 6 months.

## Safety and limits

* Public endpoints `POST /bono/ask` and `POST /bono/feedback` (CSRF checked, JSON): 24 questions per 10 minutes and 200 per day per connection; questions are cut to 500 characters and stripped of markup; answers are inserted into the page as text, never as HTML. `/bono/` is disallowed in robots.txt.
* Bono only knows what the public website and the settings say: it cannot see private pages, accounts, orders or payments.

Code: `app/services/Bono/*`, `app/controllers/{Web,Admin}/BonoController.php`, `app/views/partials/bono.php`, `app/views/admin/bono/index.php`, `assets/js/bono.js`, `scss/components/_bono.scss`, `bin/bono-learn.php`. Test: `bin/selftest-bono.php`.
