<?php
/**
 * Contact. Vars: $mapUrl ('' or a Google Maps embed URL). Copy: content('contact', ...) - admin: Pages > Contact.
 * Address, emails, phone numbers, WhatsApp and socials come from Settings (Organisation-wide details).
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/contact.js')) ?>" defer></script>
<?php $this->end();

$detailsOn = section_on('contact', 'details');
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
?>
<?= partial('partials/page-hero', [
    'title'   => content('contact', 'hero.title'),
    'eyebrow' => content('contact', 'hero.eyebrow'),
    'lead'    => content('contact', 'hero.lead'),
]) ?>

<section class="section" id="contact-form">
    <div class="container">
        <div class="contact-layout<?= $detailsOn ? '' : ' contact-layout--single' ?>">
            <div class="contact-form">
                <h2 class="contact-form__title"><?= e(content('contact', 'form.title')) ?></h2>
                <?php if (trim((string) content('contact', 'form.intro')) !== ''): ?><p class="text-muted"><?= e(content('contact', 'form.intro')) ?></p><?php endif; ?>
                <form class="form-card" method="post" action="<?= e(url('contact')) ?>" novalidate data-contact-form>
                    <?= csrf_field() ?>
                    <?= honeypot_field() ?>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'id' => 'contact-name', 'label' => content('contact', 'form.name_label'), 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'id' => 'contact-email', 'label' => content('contact', 'form.email_label'), 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'id' => 'contact-phone', 'label' => content('contact', 'form.phone_label'), 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40]) ?>
                        <?= partial('partials/form-field', ['name' => 'subject', 'id' => 'contact-subject', 'label' => content('contact', 'form.subject_label'), 'maxlength' => 220]) ?>
                        <?= partial('partials/form-field', ['name' => 'message', 'id' => 'contact-message', 'label' => content('contact', 'form.message_label'), 'type' => 'textarea', 'rows' => 7, 'required' => true, 'maxlength' => 5000, 'class' => 'field--full']) ?>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn--primary btn--lg" type="submit" data-loading-text="<?= e(content('contact', 'form.sending')) ?>"><?= e(content('contact', 'form.submit')) ?></button>
                    </div>
                    <?php if (trim((string) content('contact', 'form.privacy_note')) !== ''): ?>
                        <p class="form-note mt-4"><?= e(content('contact', 'form.privacy_note')) ?>
                            <?php if (trim((string) content('contact', 'form.privacy_link_label')) !== ''): ?><a href="<?= e(url('privacy')) ?>"><?= e(content('contact', 'form.privacy_link_label')) ?></a><?php endif; ?></p>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($detailsOn): ?>
                <aside class="contact-details" aria-labelledby="details-title">
                    <h2 id="details-title"><?= e(content('contact', 'details.title')) ?></h2>
                    <?php if (trim((string) content('contact', 'details.text')) !== ''): ?><p class="text-muted"><?= e(content('contact', 'details.text')) ?></p><?php endif; ?>

                    <ul class="contact-cards">
                        <?php if ($addressLines): ?>
                            <li class="contact-card">
                                <span class="contact-card__icon"><?= icon('map-pin') ?></span>
                                <div>
                                    <h3 class="contact-card__title"><?= e(content('contact', 'details.office_title')) ?></h3>
                                    <address class="contact-card__text">
                                        <?php if ($siteName !== ''): ?><strong><?= e($siteName) ?></strong><br><?php endif; ?>
                                        <?= implode('<br>', array_map('e', $addressLines)) ?>
                                    </address>
                                </div>
                            </li>
                        <?php endif; ?>
                        <?php if ($emails): ?>
                            <li class="contact-card">
                                <span class="contact-card__icon"><?= icon('mail') ?></span>
                                <div>
                                    <h3 class="contact-card__title"><?= e(content('contact', 'details.email_title')) ?></h3>
                                    <p class="contact-card__text"><?php foreach ($emails as $em): ?><a href="mailto:<?= e($em) ?>"><?= email_html($em) ?></a><br><?php endforeach; ?></p>
                                </div>
                            </li>
                        <?php endif; ?>
                        <?php if ($phones): ?>
                            <li class="contact-card">
                                <span class="contact-card__icon"><?= icon('phone') ?></span>
                                <div>
                                    <h3 class="contact-card__title"><?= e(content('contact', 'details.phone_title')) ?></h3>
                                    <p class="contact-card__text"><?php foreach ($phones as $ph): ?><a href="<?= e(phone_href($ph)) ?>"><?= e($ph) ?></a><br><?php endforeach; ?></p>
                                </div>
                            </li>
                        <?php endif; ?>
                        <?php if ($hours !== ''): ?>
                            <li class="contact-card">
                                <span class="contact-card__icon"><?= icon('clock') ?></span>
                                <div>
                                    <h3 class="contact-card__title"><?= e(content('contact', 'details.hours_title')) ?></h3>
                                    <p class="contact-card__text"><?= e($hours) ?></p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($wa !== ''): ?>
                        <p class="mt-5"><a class="btn btn--outline" href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"><?= icon('whatsapp') ?> <?= e(content('contact', 'details.whatsapp_label')) ?></a></p>
                    <?php endif; ?>

                    <?php if ($socials): ?>
                        <div class="contact-social">
                            <h3 class="contact-card__title"><?= e(content('contact', 'details.social_title')) ?></h3>
                            <ul class="social-list social-list--dark">
                                <?php foreach ($socials as $s): ?>
                                    <li><a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($s['label']) ?>"><?= icon($s['icon']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($mapUrl !== '' || $addressLines): ?>
    <?php section_begin('contact', 'map', ['class' => 'section--tint', 'labelledby' => 'map-title']); ?>
        <div class="section__header section__header--center"><h2 id="map-title"><?= e(content('contact', 'map.title')) ?></h2></div>
        <?php if ($mapUrl !== ''): ?>
            <div class="map-frame">
                <iframe src="<?= e($mapUrl) ?>" title="<?= e(content('contact', 'map.iframe_title')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        <?php else: ?>
            <div class="map-fallback">
                <span class="map-fallback__icon"><?= icon('map-pin', 'icon--xl') ?></span>
                <?php if (trim((string) content('contact', 'map.fallback_text')) !== ''): ?><p class="map-fallback__text"><?= e(content('contact', 'map.fallback_text')) ?></p><?php endif; ?>
                <p class="map-fallback__address"><?= e(implode(', ', $addressLines)) ?></p>
                <p><a class="btn btn--primary" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e($mapQuery) ?>" target="_blank" rel="noopener noreferrer"><?= e(content('contact', 'map.link_label')) ?> <?= icon('external-link', 'icon--sm') ?></a></p>
            </div>
        <?php endif; ?>
    <?php section_end(); ?>
<?php endif; ?>

<?php section_begin('contact', 'closing', ['class' => 'section--dark', 'container' => 'container--narrow']); ?>
    <p class="closing-line text-center"><?= e(content('contact', 'closing.text')) ?></p>
<?php section_end(); ?>
