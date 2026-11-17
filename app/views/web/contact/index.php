<?php
/**
 * Contact. Vars: $mapUrl ('' or a Google Maps embed URL). Copy: content('contact', ...) - admin: Pages > Contact.
 * Address, emails, phone numbers, WhatsApp and socials come from Settings (Organisation-wide details).
 * Order: hero (+ "Request a meeting" button) -> contact cards (+ WhatsApp, socials) -> form beside the map -> "Request a meeting" form
 * (partials/meeting-request-form, anchor #meeting-request) -> calendar -> closing line on a deep navy panel.
 */
// "Request a meeting" (docs/MEETINGS.md): shown only when the setting, the registry switch and the meeting tables all allow it
$meetingOn = \App\Services\Meetings\MeetingRequestService::enabled() && section_on('contact', 'meeting_request');

$this->start('scripts'); ?>
<script src="<?= e(asset('js/contact-hub.js')) ?>" defer></script>
<script src="<?= e(asset('js/pages/contact.js')) ?>" defer></script>
<?php if (!empty($calendar)): ?><script src="<?= e(asset('js/calendar.js')) ?>" defer></script><?php endif; ?>
<?php if ($meetingOn): ?><script src="<?= e(asset('js/meetings.js')) ?>" defer></script><?php endif; ?>
<?php $this->end();

$detailsOn = section_on('contact', 'details');
$mapOn = section_on('contact', 'map');
$siteName = (string) setting('site_name', '');
$addressLines = array_values(array_filter([trim((string) setting('address', '')), trim((string) setting('city_country', ''))]));
$emails = array_values(array_unique(array_filter([trim((string) setting('email_primary', '')), trim((string) setting('email_info', ''))])));
$phones = array_values(array_filter([trim((string) setting('phone_1', '')), trim((string) setting('phone_2', ''))]));
$extra = trim((string) content('contact', 'details.extra_phone'));
if ($extra !== '') {
    $digits = static fn(string $p): string => preg_replace('/\D+/', '', $p) ?? '';
    if (!in_array($digits($extra), array_map($digits, $phones), true)) {
        $phones[] = $extra;
    }
}
$hours = trim((string) content('contact', 'details.hours'));
$wa = whatsapp_url();
$socials = social_links('contact');
$mapQuery = rawurlencode(implode(', ', array_filter([$siteName, ...$addressLines])));
$heroImg = content_img('contact', 'hero.image');

// the contact tiles of the connect hub (partials/contact-hub): office, e-mail, phone, hours and WhatsApp, each with what a visitor does next
$mapsHref = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(implode(', ', array_filter([$siteName, ...$addressLines])));
$tiles = [];
if ($addressLines) {
    $tiles[] = ['key' => 'office', 'icon' => 'map-pin', 'label' => (string) content('contact', 'details.office_title'),
        'lines' => [['html' => '<address class="cx-tile__value cx-tile__value--plain">' . ($siteName !== '' ? '<strong>' . e($siteName) . '</strong><br>' : '') . implode('<br>', array_map('e', $addressLines)) . '</address>'],
                    ['text' => (string) content('contact', 'map.link_label'), 'href' => $mapsHref, 'external' => true]]];
}
if ($emails) {
    $tiles[] = ['key' => 'mail', 'icon' => 'mail', 'label' => (string) content('contact', 'details.email_title'),
        'lines' => array_map(static fn(string $em): array => ['html' => '<a class="cx-tile__value" href="mailto:' . e($em) . '">' . email_html($em) . '</a>', 'copy' => $em], $emails)];
}
if ($phones) {
    $tiles[] = ['key' => 'phone', 'icon' => 'phone', 'label' => (string) content('contact', 'details.phone_title'),
        'lines' => array_map(static fn(string $ph): array => ['text' => $ph, 'href' => phone_href($ph), 'copy' => $ph], $phones)];
}
if ($wa !== '') {
    $waDigits = preg_replace('/[^0-9]/', '', (string) preg_replace('~^https?://wa\.me/~i', '', (string) setting('whatsapp', ''))) ?? '';
    $tiles[] = ['key' => 'whatsapp', 'icon' => 'whatsapp', 'label' => 'WhatsApp', 'lines' => [['text' => (string) content('contact', 'details.whatsapp_label'), 'href' => $wa, 'external' => true, 'copy' => strlen($waDigits) >= 7 ? '+' . $waDigits : '']]];
}
if ($hours !== '') {
    $tiles[] = ['key' => 'clock', 'icon' => 'clock', 'label' => (string) content('contact', 'details.hours_title'), 'lines' => [['text' => $hours]]];
}

$showMap = $mapOn && ($mapUrl !== '' || $addressLines);
// the live wording repeats the page title as the heading of the cards: show it once (the section keeps an accessible name)
$detailsTitle = trim((string) content('contact', 'details.title'));
$detailsDup = mb_strtolower(strip_highlight($detailsTitle)) === mb_strtolower(trim(strip_highlight((string) content('contact', 'hero.title'))));
$fieldMeta = [
    'name'    => ['id' => 'contact-name', 'label' => (string) content('contact', 'form.name_label')],
    'email'   => ['id' => 'contact-email', 'label' => (string) content('contact', 'form.email_label')],
    'phone'   => ['id' => 'contact-phone', 'label' => (string) content('contact', 'form.phone_label')],
    'subject' => ['id' => 'contact-subject', 'label' => (string) content('contact', 'form.subject_label')],
    'message' => ['id' => 'contact-message', 'label' => (string) content('contact', 'form.message_label')],
];
?>
<?= partial('partials/page-hero', [
    'title'   => content('contact', 'hero.title'),
    'eyebrow' => content('contact', 'hero.eyebrow'),
    'lead'    => content('contact', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 40%'] : null,
    // a quick way to the meeting request form further down (its heading, from Admin > Pages > Contact, without the underline markers)
    'actions' => $meetingOn ? [['label' => strip_highlight((string) content('contact', 'meeting_request.title')), 'url' => '#meeting-request', 'class' => 'btn--white btn--caps']] : [],
]) ?>

<?php /* ------------------------------------------------------------------ CONNECT HUB (contact tiles joined by animated connectors) */ ?>
<?php if ($detailsOn && ($tiles || $socials)): ?>
<section class="section section--navy contact-hub-section" id="contact-details"<?= $detailsDup ? ' aria-label="' . e((string) content('contact', 'details.office_title')) . '"' : ' aria-labelledby="details-title"' ?>>
    <div class="container">
        <?= partial('partials/section-head', ['eyebrow' => (string) content('contact', 'details.eyebrow'), 'title' => $detailsDup ? '' : $detailsTitle, 'lead' => (string) content('contact', 'details.text'), 'align' => 'center', 'id' => 'details-title']) ?>
        <?= partial('partials/contact-hub', ['tiles' => $tiles, 'variant' => 'page', 'copyLabel' => (string) content('contact', 'details.copy_label'), 'copiedLabel' => (string) content('contact', 'details.copied_label')]) ?>
        <?php if ($socials): ?>
            <div class="contact-extra" data-reveal>
                <div class="contact-social">
                    <span class="contact-social__title" id="contact-social-title"><?= e(content('contact', 'details.social_title')) ?></span>
                    <ul class="ia-social" aria-labelledby="contact-social-title">
                        <?php foreach ($socials as $s): ?>
                            <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($s['label']) ?>"><?= icon($s['icon']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ FORM + MAP */ ?>
<section class="section section--tint contact-form-section" id="contact-form" aria-labelledby="contact-form-title">
    <div class="container">
        <div class="contact-layout<?= $showMap ? '' : ' contact-layout--single' ?>">
            <div class="contact-form">
                <?= partial('partials/section-head', ['eyebrow' => (string) content('contact', 'form.eyebrow'), 'title' => (string) content('contact', 'form.title'), 'lead' => (string) content('contact', 'form.intro'), 'id' => 'contact-form-title', 'class' => 'contact-form__head']) ?>
                <form class="form-card ia-form" method="post" action="<?= e(url('contact')) ?>" novalidate data-contact-form>
                    <?= partial('web/inner-a/_error_summary', ['title' => (string) content('contact', 'form.error_title'), 'fields' => $fieldMeta, 'sid' => 'contact']) ?>
                    <?= csrf_field() ?>
                    <?= honeypot_field() ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'id' => 'contact-name', 'label' => $fieldMeta['name']['label'], 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'id' => 'contact-email', 'label' => $fieldMeta['email']['label'], 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'id' => 'contact-phone', 'label' => $fieldMeta['phone']['label'], 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                                                <?php $topics = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) content('contact', 'form.topics')) ?: []), static fn(string $t): bool => $t !== '')); ?>
                        <?php if ($topics): ?>
                            <div class="contact-topics" role="group" aria-labelledby="contact-topics-label">
                                <span class="contact-topics__label" id="contact-topics-label"><?= e(content('contact', 'form.topics_label')) ?></span>
                                <div class="contact-topics__chips"><?php foreach ($topics as $t): ?><button class="contact-chip" type="button" data-topic="<?= e($t) ?>" aria-pressed="false"><?= e($t) ?></button><?php endforeach; ?></div>
                            </div>
                        <?php endif; ?>
                        <?= partial('partials/form-field', ['name' => 'subject', 'attrs' => ['data-contact-subject' => true], 'id' => 'contact-subject', 'label' => $fieldMeta['subject']['label'], 'maxlength' => 220]) ?>
                        <?= partial('partials/form-field', ['name' => 'message', 'id' => 'contact-message', 'label' => $fieldMeta['message']['label'], 'type' => 'textarea', 'rows' => 7, 'required' => true, 'maxlength' => 5000, 'attrs' => ['data-contact-message' => true, 'data-counter-label' => (string) content('contact', 'form.counter_label')], 'class' => 'field--full']) ?>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e(content('contact', 'form.sending')) ?>"><?= e(content('contact', 'form.submit')) ?></button>
                    </div>
                    <?php if (trim((string) content('contact', 'form.privacy_note')) !== ''): ?>
                        <p class="form-note mt-4"><?= e(content('contact', 'form.privacy_note')) ?>
                            <?php if (trim((string) content('contact', 'form.privacy_link_label')) !== ''): ?><a href="<?= e(url('privacy')) ?>"><?= e(content('contact', 'form.privacy_link_label')) ?></a><?php endif; ?></p>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($showMap): ?>
                <aside class="contact-map" aria-labelledby="map-title" data-reveal>
                    <?= partial('partials/section-head', ['eyebrow' => (string) content('contact', 'map.eyebrow'), 'title' => (string) content('contact', 'map.title'), 'id' => 'map-title', 'class' => 'contact-form__head']) ?>
                    <?php if ($mapUrl !== ''): ?>
                        <div class="ia-map">
                            <iframe src="<?= e($mapUrl) ?>" title="<?= e(content('contact', 'map.iframe_title')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="map-fallback">
                            <span class="map-fallback__icon"><?= icon('map-pin', 'icon--xl') ?></span>
                            <?php if (trim((string) content('contact', 'map.fallback_text')) !== ''): ?><p class="map-fallback__text"><?= e(content('contact', 'map.fallback_text')) ?></p><?php endif; ?>
                            <p class="map-fallback__address"><?= e(implode(', ', $addressLines)) ?></p>
                            <p><a class="btn btn--cta btn--caps" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($mapQuery) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('contact', 'map.link_label')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
                        </div>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php /* ------------------------------------------------------------------ REQUEST A MEETING (docs/MEETINGS.md; prints nothing when switched off) */ ?>
<?php if ($meetingOn): ?>
    <?= partial('partials/meeting-request-form') ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ CALENDAR (compact month + coming up; docs/CALENDAR.md) */ ?>
<?php if (!empty($calendar)): ?>
    <?= partial('partials/calendar-embed', ['calendar' => $calendar]) ?>
<?php endif; ?>

<?php /* ------------------------------------------------------------------ CLOSING LINE */ ?>
<?php section_begin('contact', 'closing', ['class' => 'contact-closing']); ?>
    <div class="ia-panel contact-closing__panel" data-reveal>
        <p class="closing-line"><?= e(content('contact', 'closing.text')) ?></p>
    </div>
<?php section_end(); ?>
