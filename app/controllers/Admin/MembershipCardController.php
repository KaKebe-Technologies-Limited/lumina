<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\AuditLog;
use App\Core\Database;
use App\Core\Flash;
use App\Core\HttpException;
use App\Core\ImageOptimizer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Settings;
use App\Core\UploadException;
use App\Core\Uploader;
use App\Models\MembershipType;
use App\Services\ImageCleanup;
use App\Services\Membership\Card\CardPng;
use App\Services\Membership\Card\PdfWriter;
use App\Services\Membership\Card\CardPreview;
use App\Services\Membership\Card\CardSettings;
use App\Services\Membership\MembershipMail;
use App\Services\Membership\MembershipService;

/**
 * ADMIN > Membership > MEMBERSHIP CARD (the designer). OWNER: CARD agent (this file, app/views/admin/membership-card/*, assets/js/admin-membership-card.js,
 * scss/admin/_membership-card.scss, app/services/Membership/Card/CardPreview.php). Ability: members.card (admin only by default). Contract: docs/MEMBERSHIP_CARD.md.
 *
 * The designer edits the settings of CardSettings::DEFS (every one has a default that reproduces the owner's reference card), arranged in seven tabs:
 * logo and colours, words, blocks and tiles, footer, member numbers, issue rules, the e-mail. Saving goes through CardSettings::save() (sanitising, number-format
 * check, audit by key NAMES only) after two extra checks of this screen: the logo is run through ImageOptimizer::logo() (trimmed, shrunk, metadata removed) and a
 * colour combination that would make text unreadable is REFUSED (contrast under 2:1, or 3:1 for the navy that also colours the QR code), while a combination
 * that is merely hard to read only produces a warning.
 *
 * The PREVIEW is the REAL card engine drawing a fictional member (CardPreview scenarios, watermarked "SAMPLE CARD - fictional member, not valid for
 * verification"): POST preview remembers the form values that are not saved yet in the session (nothing is stored in the database) and answers with JSON
 * (errors, contrast, the example member numbers, the rendered e-mail), GET preview.pdf / preview.png draw the sample with them (same origin, so the PDF sits
 * in an <iframe>: the CSP already allows frame-src 'self'). The test e-mail goes ONLY to the signed-in administrator's own address.
 * "Restore the reference design" and the per-tab "Back to the reference" buttons post `reset` to the save route (no extra route is needed).
 */
final class MembershipCardController extends AdminController
{
    private const SESSION_KEY = 'membership_card_preview';

    /** Tabs of the designer: key => label, icon, one-line help. Order = order on screen. */
    public const TABS = [
        'look'    => ['label' => 'Logo & colours', 'icon' => 'image', 'help' => 'The picture at the top left and the colours of the card.'],
        'words'   => ['label' => 'Words', 'icon' => 'file-text', 'help' => 'The name of the organisation, the tagline, the labels and the status wording.'],
        'layout'  => ['label' => 'Blocks & tiles', 'icon' => 'layout', 'help' => 'Choose what the card shows: the photo, the QR code and the three information tiles.'],
        'footer'  => ['label' => 'Footer & contact', 'icon' => 'phone', 'help' => 'The small grey lines at the bottom left of the card.'],
        'numbers' => ['label' => 'Member numbers', 'icon' => 'tag', 'help' => 'How membership numbers are built, and the code of each membership type.'],
        'rules'   => ['label' => 'When it is issued', 'icon' => 'award', 'help' => 'When a card is made and e-mailed, and what is needed first.'],
        'email'   => ['label' => 'The e-mail', 'icon' => 'mail', 'help' => 'The message that carries the card PDF to the member.'],
    ];

    /** The settings of each tab (all keys of CardSettings::DEFS, each exactly once). @var array<string,list<string>> */
    public const TAB_KEYS = [
        'look'    => ['membership_card_logo', 'membership_card_navy', 'membership_card_accent', 'membership_card_gold', 'membership_card_green', 'membership_card_tile', 'membership_card_label_color', 'membership_card_muted'],
        'words'   => ['membership_card_org_name', 'membership_card_tagline', 'membership_card_label', 'membership_card_label_member', 'membership_card_label_from', 'membership_card_location_format',
            'membership_card_label_number', 'membership_card_label_valid_until', 'membership_card_scan_text', 'membership_card_renews_text', 'membership_card_no_expiry_text',
            'membership_card_status_active', 'membership_card_status_expired', 'membership_card_status_suspended', 'membership_card_status_revoked', 'membership_card_panel_text',
            'membership_card_adverb_annual', 'membership_card_adverb_monthly', 'membership_card_adverb_quarterly'],
        'layout'  => ['membership_card_show_photo', 'membership_card_show_qr', 'membership_card_tile_1', 'membership_card_tile_2', 'membership_card_tile_3',
            'membership_card_label_member_since', 'membership_card_label_payment', 'membership_card_label_fee', 'membership_card_label_type', 'membership_card_label_district',
            'membership_card_pay_paid', 'membership_card_pay_waived', 'membership_card_pay_pending', 'membership_card_pay_free', 'membership_card_pay_partial', 'membership_card_pay_tbd',
            'membership_card_fee_free', 'membership_card_fee_tbd', 'membership_card_fee_per_year', 'membership_card_fee_per_month', 'membership_card_fee_per_quarter', 'membership_card_fee_for_life', 'membership_card_fee_one_off',
            'membership_card_period_annual', 'membership_card_period_monthly', 'membership_card_period_quarterly', 'membership_card_period_lifetime', 'membership_card_period_one_time'],
        'footer'  => ['membership_card_footer_1', 'membership_card_footer_2', 'membership_card_footer_3', 'membership_card_support_phone', 'membership_card_support_email',
            'membership_card_joining_paid', 'membership_card_joining_due', 'membership_card_joining_waived'],
        'numbers' => ['membership_card_number_format'],
        'rules'   => ['membership_card_trigger', 'membership_card_reissue_renewal', 'membership_card_require_photo', 'membership_photo_required', 'membership_proof_retention_days'],
        'email'   => ['membership_card_email_subject', 'membership_card_email_body'],
    ];

    /** What "Restore the reference design" resets: the LOOK of the card (not the numbering, the issue rules or the e-mail). */
    private const APPEARANCE = ['look', 'words', 'layout', 'footer'];

    /** Colour themes offered as a starting point (each can be saved: none is below the refuse limits). name = its title; the other keys are the short colour names => hex. */
    public const THEMES = [
        'reference' => ['name' => 'Reference (navy and orange)', 'navy' => '#0F1B33', 'accent' => '#E0642C', 'gold' => '#C9973F', 'green' => '#22A35A', 'tile' => '#F4F6FA', 'label' => '#6A7389', 'muted' => '#B9C2DA'],
        'royal'     => ['name' => 'Royal blue', 'navy' => '#162B6B', 'accent' => '#E0642C', 'gold' => '#C9973F', 'green' => '#1E9A58', 'tile' => '#EFF3FB', 'label' => '#5B678A', 'muted' => '#BCC8EA'],
        'forest'    => ['name' => 'Forest green', 'navy' => '#0F2D22', 'accent' => '#C8641B', 'gold' => '#C9973F', 'green' => '#1F8A4C', 'tile' => '#F2F6F3', 'label' => '#5F6E66', 'muted' => '#B6CBBF'],
        'burgundy'  => ['name' => 'Burgundy', 'navy' => '#3A1220', 'accent' => '#D4572A', 'gold' => '#C9973F', 'green' => '#1F8F54', 'tile' => '#F8F3F3', 'label' => '#7A6469', 'muted' => '#D9C3CB'],
        'charcoal'  => ['name' => 'Charcoal and teal', 'navy' => '#1F2933', 'accent' => '#0F8B8D', 'gold' => '#C9973F', 'green' => '#218C55', 'tile' => '#F3F4F6', 'label' => '#667085', 'muted' => '#C3CAD5'],
    ];

    /**
     * The colour pairs that carry text on the card. [id, colour keys involved, what, foreground, background, advised minimum ratio, refuse below].
     * Foreground / background are short colour names (navy accent gold green tile label muted) or a fixed hex. The advised minimums are those of
     * CardSettings::warnings(): 4.5:1 for small text (WCAG AA), 3:1 only for the large initials. The owner's REFERENCE colours do not reach 4.5:1 on four of them
     * (tagline 2.6, type line 3.5, status pill 3.3, tile labels 4.4): they stay the default because they are the owner's design, and this screen says so plainly
     * instead of calling them "good". "Refuse below" is what this screen will not save at all.
     */
    public const CONTRAST = [
        ['navy_white',   ['navy'],            'The white member number and dates on the side panel',       '#FFFFFF', 'navy',  4.5, 3.0],
        ['qr',           ['navy'],            'The QR code and the name of the member (the side panel colour on white)',             'navy',    '#FFFFFF', 4.5, 3.0],
        ['muted_navy',   ['muted', 'navy'],   'The small text on the side panel',                       'muted',   'navy',  4.5, 2.0],
        ['green_white',  ['green'],           'The white status text on the green pill',                              '#FFFFFF', 'green', 4.5, 2.0],
        ['accent_white', ['accent'],          'The white initials on the accent photo block',                         '#FFFFFF', 'accent', 3.0, 2.0],
        ['accent_text',  ['accent'],          'The membership type line (accent colour on white)',                    'accent',  '#FFFFFF', 4.5, 2.0],
        ['gold_white',   ['gold'],            'The tagline (gold on white)',                                          'gold',    '#FFFFFF', 4.5, 1.8],
        ['label_white',  ['label'],           'The small grey labels on white',                                       'label',   '#FFFFFF', 4.5, 2.0],
        ['label_tile',   ['label', 'tile'],   'The small grey labels on the information tiles',                       'label',   'tile',  4.5, 2.0],
        ['navy_tile',    ['navy', 'tile'],    'The values on the information tiles (navy on the tile colour)',        'navy',    'tile',  7.0, 3.0],
    ];

    // ------------------------------------------------------------------------------------------------ screens

    public function index(Request $request): Response
    {
        $this->authorize('members.card');
        Session::forget(self::SESSION_KEY);                                // a reload shows the SAVED card, like the form
        $old = $this->oldInput();
        $values = array_merge(CardSettings::resolve(), $old);              // what was typed stays on screen after a failed save
        $valid = CardSettings::resolve($old);                              // the same, cleaned: for the rules that need real colours
        $types = [];
        foreach (MembershipType::all() as $t) {
            $types[] = ['id' => (int) $t['id'], 'type' => $t, 'code' => MembershipService::typeCode($t), 'next' => MembershipService::numberPreview($t), 'active' => (int) ($t['is_active'] ?? 1) === 1];
        }
        $errorTabs = [];
        foreach (array_keys(errors()) as $k) {
            foreach (self::TAB_KEYS as $tab => $keys) {
                if (in_array($k, $keys, true)) {
                    $errorTabs[$tab] = true;
                }
            }
        }
        $png = CardPng::available();
        [$mailSubject, $mailText, $mailHtml] = MembershipMail::renderCard(self::sampleMember($this->user()), $old, true);
        return $this->render('admin/membership-card/index', [
            'values'      => $values,
            'defs'        => CardSettings::DEFS,
            'tabs'        => self::TABS,
            'tabKeys'     => self::TAB_KEYS,
            'errorTabs'   => $errorTabs,
            'themes'      => self::THEMES,
            'tileOptions' => CardSettings::TILE_OPTIONS,
            'triggers'    => CardSettings::TRIGGERS,
            'contrast'    => self::contrastRows($valid),
            'contrastDefs' => self::contrastDefs(),
            'types'       => $types,
            'prefix'      => MembershipService::prefix(),
            'numberDefault' => CardSettings::DEFAULT_NUMBER_FORMAT,
            'emailPlaceholders' => CardSettings::EMAIL_PLACEHOLDERS,
            'emailPreview' => ['subject' => $mailSubject, 'text' => $mailText, 'html' => $mailHtml],
            'scenarios'   => CardPreview::SCENARIOS,
            'stats'       => $this->cardStats(),
            'caps'        => ['png' => $png, 'ttf' => CardPng::hasTrueType()],
            'stale'       => $old !== [],
            'logoIsDefault' => trim((string) $values['membership_card_logo']) === '',
            'previewPdf'  => url('admin/membership-card/preview.pdf', ['v' => time()]),
            'previewPng'  => $png ? url('admin/membership-card/preview.png', ['v' => time()]) : '',
            'adminEmail'  => (string) ($this->user()['email'] ?? ''),
            'defaults'    => CardSettings::defaults(),
        ], ['title' => 'Membership card', 'nav' => 'membership_card', 'breadcrumbs' => [['Membership card', null]]]);
    }

    /** Save, or (with `reset`) go back to the reference design for one tab or for the whole look of the card. */
    public function save(Request $request): Response
    {
        $this->authorize('members.card');
        $tab = $this->tabFrom($request->string('tab'));
        $reset = $request->string('reset');
        if ($reset !== '') {
            return $this->reset($reset, $tab);
        }

        $input = $request->all();
        $current = CardSettings::get('membership_card_logo');
        try {
            $img = ImageCleanup::resolve($request, 'membership_card_logo', $current !== '' ? $current : null);
        } catch (UploadException $e) {
            $this->fail(['membership_card_logo' => $e->getMessage()], $this->back_to($tab));
        }
        $logo = (string) ($img['value'] ?? '');
        $optimised = null;
        if ($logo !== '' && $img['changed']) {
            try {
                $optimised = ImageOptimizer::logo(UPLOAD_PATH . '/' . $logo, ['dir' => 'membership/card', 'trim' => true]);
                foreach ((array) $optimised['files'] as $f) {          // only the 1x picture is used on a card
                    if ($f !== $optimised['logo']) {
                        Uploader::delete((string) $f);
                    }
                }
                $logo = (string) $optimised['logo'];
            } catch (UploadException $e) {
                $this->fail(['membership_card_logo' => $e->getMessage()], $this->back_to($tab));
            }
            // Without the image library (GD) the card writer can embed only plain JPEG and PNG pictures: WebP, GIF and interlaced / 16-bit PNG would be silently replaced by
            // the bundled seal on every card, so refuse them here instead of letting the owner believe the logo is used.
            if (!function_exists('imagecreatefromstring') && PdfWriter::imageInfo((string) @file_get_contents(UPLOAD_PATH . '/' . $logo)) === null) {
                ImageCleanup::discardUpload($logo);
                $this->fail(['membership_card_logo' => 'This server has no image library, so the card can only use a plain JPG or PNG logo (not WebP, GIF or an interlaced PNG). Please save the logo as a normal JPG or PNG and upload it again.'], $this->back_to($tab));
            }
        }
        $input['membership_card_logo'] = $logo;
        // the e-mail message has more lines than CardSettings::sanitise() keeps (6): it is cleaned and saved here (see CardPreview::cleanMessage)
        $message = null;
        if ($request->has('membership_card_email_body')) {
            $message = CardPreview::cleanMessage((string) $request->input('membership_card_email_body'));
            unset($input['membership_card_email_body']);
        }

        $clean = CardSettings::sanitise($input, true);
        $errors = $clean['errors'];
        if ($message === '') {
            $errors['membership_card_email_body'] = 'The e-mail message cannot be empty.';
        }
        foreach (self::contrastErrors(CardSettings::resolve(array_diff_key($clean['values'], $errors))) as $k => $msg) {
            $errors[$k] ??= $msg;
        }
        if ($errors !== []) {
            if ($optimised !== null) {
                ImageCleanup::discardUpload($logo);                    // the optimised copy is ours; the uploaded original stays referenced by the form
            }
            $this->fail($errors, $this->back_to($tab));
        }

        $res = CardSettings::save($input, $this->userId());
        if (!$res['ok']) {                                              // unreachable after the checks above, kept as a guard
            $this->fail($res['errors'], $this->back_to($tab));
        }
        $changed = $res['changed'];
        if ($message !== null && $message !== CardSettings::get('membership_card_email_body')) {
            $message === CardSettings::default('membership_card_email_body') ? Settings::reset('membership_card_email_body') : Settings::set('membership_card_email_body', $message);
            $changed[] = 'membership_card_email_body';
            AuditLog::record('membership_card.settings', 'settings', null, 'Changed the membership card settings', ['keys' => ['membership_card_email_body'], 'user_id' => $this->userId()]);
        }
        if (in_array('membership_card_logo', $res['changed'], true)) {
            ImageCleanup::release($current);                            // the previous logo file, once nothing references it
            $source = (string) ($img['value'] ?? '');
            if ($source !== '' && $source !== $logo) {
                ImageCleanup::release($source);                         // the un-optimised upload the optimised logo came from
            }
        }
        Session::forget(self::SESSION_KEY);
        Flash::success($changed === []
            ? 'Nothing changed.'
            : 'The membership card settings were saved. New cards, downloads and e-mails use them at once. A card that was already e-mailed does not change; use "Re-issue" on a member to refresh that member\'s own details.');
        if ($optimised !== null && !empty($optimised['notice'])) {
            Flash::warning((string) $optimised['notice']);
        }
        if ($optimised !== null && in_array('low_resolution', (array) ($optimised['warnings'] ?? []), true)) {
            Flash::warning('The logo is small, so it may look soft on a printed card. A picture at least 320 pixels wide is better.');
        }
        return $this->redirect($this->back_to($tab));
    }

    /** Remember the UNSAVED form values for the preview (nothing is stored in the database). Returns JSON with the preview URLs and the live checks. */
    public function preview(Request $request): Response
    {
        $this->authorize('members.card');
        $clean = CardSettings::sanitise($request->all(), true);
        $errors = $clean['errors'];
        $values = array_diff_key($clean['values'], $errors);
        $contrastErrors = self::contrastErrors(CardSettings::resolve($values));
        foreach ($contrastErrors as $k => $msg) {
            $errors[$k] = $msg;
            unset($values[$k]);                                          // the preview keeps the saved colour until the new one is acceptable
        }
        if ($request->has('membership_card_email_body')) {              // the message may have more lines than CardSettings::sanitise() keeps (see CardPreview::cleanMessage)
            $message = CardPreview::cleanMessage((string) $request->input('membership_card_email_body'));
            if ($message === '') {
                $errors['membership_card_email_body'] = 'The e-mail message cannot be empty.';
                unset($values['membership_card_email_body']);
            } else {
                $values['membership_card_email_body'] = $message;
            }
        }
        $logo = (string) ($values['membership_card_logo'] ?? '');       // a logo uploaded by the picture field is a real file below uploads/ already
        if ($logo !== '' && !is_file(UPLOAD_PATH . '/' . $logo)) {
            unset($values['membership_card_logo']);
        }
        $scenario = CardPreview::scenario($request->string('scenario'));
        Session::put(self::SESSION_KEY, ['values' => $values, 'scenario' => $scenario]);
        $resolved = CardSettings::resolve($values);

        $numbers = [];
        $format = strtoupper((string) ($values['membership_card_number_format'] ?? CardSettings::get('membership_card_number_format')));
        if (CardSettings::numberFormatError($format) === null) {
            foreach (MembershipType::all() as $t) {
                $numbers[(string) (int) $t['id']] = CardSettings::expandNumber($format, MembershipService::prefix(), MembershipService::typeCode($t), (int) date('Y'), 1);
            }
        }
        [$subject, $text, $html] = MembershipMail::renderCard(self::sampleMember($this->user()), $values, true);
        $contrast = self::contrastRows($resolved);
        return $this->json([
            'ok'       => true,
            'errors'   => $errors,
            'contrast' => array_map(static fn(array $r): array => ['id' => $r['id'], 'ratio' => $r['ratio'], 'status' => $r['status']], $contrast),
            'summary'  => self::contrastSummary($contrast),
            'scenario' => $scenario,
            'pdf'      => url('admin/membership-card/preview.pdf', ['v' => (int) (microtime(true) * 1000)]),
            'png'      => CardPng::available() ? url('admin/membership-card/preview.png', ['v' => (int) (microtime(true) * 1000)]) : '',
            'numbers'  => $numbers,
            'email'    => ['subject' => $subject, 'text' => $text, 'html' => $html],
        ]);
    }

    public function previewPdf(Request $request): Response
    {
        $this->authorize('members.card');
        $state = $this->previewState();
        $pdf = CardPreview::pdf($state['scenario'], $state['values']);
        $disposition = $request->string('download') === '1' ? 'attachment' : 'inline';
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf', 'Content-Length' => (string) strlen($pdf), 'Content-Disposition' => $disposition . '; filename="membership-card-sample.pdf"',
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    public function previewPng(Request $request): Response
    {
        $this->authorize('members.card');
        $state = $this->previewState();
        $png = CardPreview::png($state['scenario'], $state['values']);
        if ($png === null) {
            throw new HttpException(404, 'The image library (GD) is not available on this server.');
        }
        return new Response($png, 200, ['Content-Type' => 'image/png', 'Content-Length' => (string) strlen($png), 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function testEmail(Request $request): Response
    {
        $this->authorize('members.card');
        $to = (string) ($this->user()['email'] ?? '');
        if ($to === '') {
            Flash::error('Your administrator account has no e-mail address.');
            return $this->redirect('admin/membership-card#tab-email');
        }
        if (!RateLimit::attempt('member-card-sample:' . ($this->userId() ?? 0), 10, 3600)) {
            Flash::error('Too many test cards. Please try again in a few minutes.');
            return $this->redirect('admin/membership-card#tab-email');
        }
        $state = $this->previewState();
        $ok = MembershipMail::cardSample($to, CardPreview::pdf($state['scenario'], $state['values']), $state['values']);
        $ok ? Flash::success('A test card was sent to ' . $to . '. It uses what the preview shows and is marked as a test.') : Flash::error('The test e-mail could not be sent. Check the mail settings (Site settings > Contact) and try again.');
        if ($ok) {
            AuditLog::record('membership_card.test_email', 'settings', null, 'Sent a test membership card e-mail', ['user_id' => $this->userId()]);
        }
        return $this->redirect('admin/membership-card#tab-email');
    }

    // ------------------------------------------------------------------------------------------------ reset

    private function reset(string $what, string $tab): Response
    {
        if ($what !== 'appearance' && !isset(self::TAB_KEYS[$what])) {
            Flash::error('That part of the card cannot be reset.');
            return $this->redirect($this->back_to($tab));
        }
        $groups = $what === 'appearance' ? self::APPEARANCE : [$what];
        $keys = [];
        foreach ($groups as $g) {
            $keys = array_merge($keys, self::TAB_KEYS[$g]);
        }
        $oldLogo = CardSettings::get('membership_card_logo');
        $changed = [];
        foreach ($keys as $k) {
            if (CardSettings::get($k) !== CardSettings::default($k)) {
                Settings::reset($k);
                $changed[] = $k;
            }
        }
        if ($changed !== []) {
            AuditLog::record('membership_card.reset', 'settings', null, 'Restored the membership card settings to the reference design', ['part' => $what, 'keys' => $changed, 'user_id' => $this->userId()]);
            if (in_array('membership_card_logo', $changed, true)) {
                ImageCleanup::release($oldLogo);
            }
        }
        Session::forget(self::SESSION_KEY);
        $label = $what === 'appearance' ? 'The whole look of the card' : (self::TABS[$what]['label'] ?? 'These settings');
        Flash::success($changed === [] ? $label . ': nothing to reset, it already is the reference design.' : $label . ' went back to the reference design.');
        return $this->redirect($this->back_to($what === 'appearance' ? 'look' : $tab));
    }

    // ------------------------------------------------------------------------------------------------ contrast

    /**
     * Every colour pair with its ratio and a status: ok (meets the advised minimum), warn (below it), fail (below the "refuse" limit: cannot be saved).
     * @param array<string,string> $values resolved settings @return list<array<string,mixed>>
     */
    public static function contrastRows(array $values): array
    {
        $c = CardSettings::colors($values);
        $out = [];
        foreach (self::CONTRAST as [$id, $keys, $what, $fg, $bg, $min, $fail]) {
            $f = $c[$fg] ?? $fg;
            $b = $c[$bg] ?? $bg;
            $ratio = CardSettings::contrast($f, $b);
            $out[] = ['id' => $id, 'keys' => $keys, 'what' => $what, 'fg' => $f, 'bg' => $b, 'min' => $min, 'fail' => $fail, 'ratio' => round($ratio, 2), 'status' => $ratio < $fail ? 'fail' : ($ratio < $min ? 'warn' : 'ok')];
        }
        return $out;
    }

    /** The pair definitions for the script (names instead of hex, so it can recompute as a colour is typed). @return list<array<string,mixed>> */
    public static function contrastDefs(): array
    {
        $out = [];
        foreach (self::CONTRAST as [$id, $keys, $what, $fg, $bg, $min, $fail]) {
            $out[] = ['id' => $id, 'keys' => $keys, 'what' => $what, 'fg' => $fg, 'bg' => $bg, 'min' => $min, 'fail' => $fail];
        }
        return $out;
    }

    /** @param list<array<string,mixed>> $rows @return array{ok:int,warn:int,fail:int} */
    public static function contrastSummary(array $rows): array
    {
        $s = ['ok' => 0, 'warn' => 0, 'fail' => 0];
        foreach ($rows as $r) {
            $s[(string) $r['status']]++;
        }
        return $s;
    }

    /** Colour fields that cannot be saved because the text on them would be unreadable. @param array<string,string> $values resolved settings @return array<string,string> field => message */
    public static function contrastErrors(array $values): array
    {
        $errors = [];
        $keyOf = static fn(string $short): string => $short === 'label' ? 'membership_card_label_color' : 'membership_card_' . $short;
        foreach (self::contrastRows($values) as $r) {
            if ($r['status'] !== 'fail') {
                continue;
            }
            $msg = $r['what'] . ' would be unreadable (contrast ' . number_format((float) $r['ratio'], 1) . ':1; at least ' . number_format((float) $r['fail'], 1) . ':1 is needed). Choose a colour with more difference.';
            foreach ($r['keys'] as $short) {
                $errors[$keyOf((string) $short)] ??= $msg;
            }
        }
        return $errors;
    }

    // ------------------------------------------------------------------------------------------------ helpers

    /** The unsaved values and scenario of the last preview request (empty values = the saved settings). @return array{values:array<string,string>,scenario:string} */
    private function previewState(): array
    {
        $s = Session::get(self::SESSION_KEY, []);
        $values = is_array($s) && is_array($s['values'] ?? null) ? array_map('strval', $s['values']) : [];
        return ['values' => $values, 'scenario' => CardPreview::scenario(is_array($s) ? ($s['scenario'] ?? null) : null)];
    }

    /** Old input of a failed save (so the form keeps what was typed), restricted to the card settings keys. @return array<string,string> */
    private function oldInput(): array
    {
        $out = [];
        foreach (array_keys(CardSettings::DEFS) as $k) {
            $v = old($k, null);
            if (is_scalar($v)) {
                $out[$k] = (string) $v;
            }
        }
        return $out;
    }

    private function tabFrom(string $tab): string
    {
        return isset(self::TABS[$tab]) ? $tab : '';
    }

    /** The designer URL, back on the tab the administrator was working on. */
    private function back_to(string $tab): string
    {
        return 'admin/membership-card' . ($tab !== '' ? '#tab-' . $tab : '');
    }

    /** @return array{issued:int,reissue:int,revoked:int} how many cards exist (for the hints; zeros when the table is not ready) */
    private function cardStats(): array
    {
        try {
            return [
                'issued'  => (int) Database::value("SELECT COUNT(*) FROM members WHERE card_status = 'issued'", [], 0),
                'reissue' => (int) Database::value("SELECT COUNT(*) FROM members WHERE card_status = 'issued' AND card_needs_reissue = 1", [], 0),
                'revoked' => (int) Database::value("SELECT COUNT(*) FROM members WHERE card_status = 'revoked'", [], 0),
            ];
        } catch (\Throwable) {
            return ['issued' => 0, 'reissue' => 0, 'revoked' => 0];
        }
    }

    /** The fictional member behind the e-mail preview (the same one MembershipMail::cardSample() uses). @param array<string,mixed> $user @return array<string,mixed> */
    private static function sampleMember(array $user): array
    {
        return [
            'id' => 0, 'full_name' => 'Amina Akello', 'email' => (string) ($user['email'] ?? ''), 'application_ref' => 'LAF-APP-2026-00000', 'type_name' => 'Individual Member',
            'membership_number' => 'LAF-IND-26-0001', 'start_date' => '2026-10-20', 'expiry_date' => '2027-10-20', 'photo_path' => null,
        ];
    }
}
