<?php
/**
 * Contact. Vars: $mapUrl ('' or a Google Maps embed URL). Copy: content('contact', ...) - admin: Pages > Contact.
 * Address, emails, phone numbers, WhatsApp and socials come from Settings (Organisation-wide details).
 * Order: hero -> contact cards (+ WhatsApp, socials) -> form beside the map -> closing line on a deep navy panel.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/contact.js')) ?>" defer></script>
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
$socials = social_links();
$mapQuery = rawurlencode(implode(', ', array_filter([$siteName, ...$addressLines])));
$heroImg = content_img('contact', 'hero.image');

// the contact cards: [icon, heading, ready-made SAFE html]
$cards = [];
if ($addressLines) {
    $cards[] = ['icon' => 'map-pin', 'title' => (string) content('contact', 'details.office_title'),
        'html' => '<address class="contact-card__text">' . ($siteName !== '' ? '<strong>' . e($siteName) . '</strong><br>' : '') . implode('<br>', array_map('e', $addressLines)) . '</address>'];
}
if ($emails) {
    $cards[] = ['icon' => 'mail', 'title' => (string) content('contact', 'details.email_title'),
        'html' => '<p class="contact-card__text">' . implode('<br>', array_map(static fn(string $em): string => '<a href="mailto:' . e($em) . '">' . email_html($em) . '</a>', $emails)) . '</p>'];
}
if ($phones) {
    $cards[] = ['icon' => 'phone', 'title' => (string) content('contact', 'details.phone_title'),
        'html' => '<p class="contact-card__text">' . implode('<br>', array_map(static fn(string $ph): string => '<a href="' . e(phone_href($ph)) . '">' . e($ph) . '</a>', $phones)) . '</p>'];
}
if ($hours !== '') {
    $cards[] = ['icon' => 'clock', 'title' => (string) content('contact', 'details.hours_title'), 'html' => '<p class="contact-card__text">' . e($hours) . '</p>'];
}
$cardsClass = count($cards) >= 4 ? ' feature-row--4' : (count($cards) === 2 ? ' feature-row--2' : '');

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
]) ?>

<?php /* ------------------------------------------------------------------ CONTACT CARDS */ ?>
<?php if ($detailsOn && ($cards || $wa !== '' || $socials)): ?>
<section class="section contact-details" id="contact-details"<?= $detailsDup ? ' aria-label="' . e((string) content('contact', 'details.office_title')) . '"' : ' aria-labelledby="details-title"' ?>>
    <div class="container">
        <?= partial('partials/section-head', ['eyebrow' => (string) content('contact', 'details.eyebrow'), 'title' => $detailsDup ? '' : $detailsTitle, 'lead' => (string) content('contact', 'details.text'), 'align' => 'center', 'id' => 'details-title']) ?>
        <?php if ($cards): ?>
            <div class="feature-row contact-cards<?= $cardsClass ?>">
                <?php foreach ($cards as $c): ?>
                    <article class="feature-card contact-card" data-reveal>
                        <span class="feature-card__icon"><?= icon($c['icon']) ?></span>
                        <div class="feature-card__body">
                            <h3 class="feature-card__title"><?= e($c['title']) ?></h3>
                            <?= $c['html'] ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($wa !== '' || $socials): ?>
            <div class="contact-extra" data-reveal>
                <?php if ($wa !== ''): ?><a class="btn btn--outline btn--caps" href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"><?= icon('whatsapp', 'icon--sm') ?> <?= e(content('contact', 'details.whatsapp_label')) ?></a><?php endif; ?>
                <?php if ($socials): ?>
                    <div class="contact-social">
                        <span class="contact-social__title" id="contact-social-title"><?= e(content('contact', 'details.social_title')) ?></span>
                        <ul class="ia-social" aria-labelledby="contact-social-title">
                            <?php foreach ($socials as $s): ?>
                                <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($s['label']) ?>"><?= icon($s['icon']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
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
                        <?= partial('partials/form-field', ['name' => 'subject', 'id' => 'contact-subject', 'label' => $fieldMeta['subject']['label'], 'maxlength' => 220]) ?>
                        <?= partial('partials/form-field', ['name' => 'message', 'id' => 'contact-message', 'label' => $fieldMeta['message']['label'], 'type' => 'textarea', 'rows' => 7, 'required' => true, 'maxlength' => 5000, 'class' => 'field--full']) ?>
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

<?php /* ------------------------------------------------------------------ CLOSING LINE */ ?>
<?php section_begin('contact', 'closing', ['class' => 'contact-closing']); ?>
    <div class="ia-panel contact-closing__panel" data-reveal>
        <p class="closing-line"><?= e(content('contact', 'closing.text')) ?></p>
    </div>
<?php section_end(); ?>
