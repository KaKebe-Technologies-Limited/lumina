<?php
/**
 * AMBASSADORS (/involvement/ambassadors). Copy: content('ambassadors', ...) (Admin > Pages > Ambassadors). Vars: $regions (key => label), $countries (region => list),
 * $currencies (code => label), $directory (approved ambassadors who agreed to be shown), $countryCount, $faqs.
 * The form posts to /involvement/ambassadors (honeypot + throttle + CSRF) and is stored in Admin > Inbox > Ambassadors.
 * Order: hero > highlights strip > what is an ambassador > what they do > yearly pledge > how it works > benefits > anywhere in the world > directory > form > FAQ > donate band.
 */

use App\Models\AmbassadorApplication as Amb;

$heroImg = content_img('ambassadors', 'hero.image');
$b1 = content_btn('ambassadors', 'hero.cta');
$b2 = content_btn('ambassadors', 'hero.cta2');
$actions = [];
if ($b1['has']) {
    $actions[] = ['label' => $b1['label'], 'url' => $b1['url'], 'class' => 'btn--cta btn--caps'];
}
if ($b2['has']) {
    $actions[] = ['label' => $b2['label'], 'url' => $b2['url'], 'class' => 'btn--photo btn--caps'];
}
$titled = static fn(array $rows): array => array_values(array_filter($rows, static fn(array $r): bool => trim((string) ($r['title'] ?? '')) !== ''));
$cardsOf = static function (string $key) use ($titled): array {
    return $titled(content_list('ambassadors', $key));
};
?>
<?= partial('partials/page-hero', [
    'eyebrow' => (string) content('ambassadors', 'hero.eyebrow'),
    'title'   => (string) content('ambassadors', 'hero.title'),
    'lead'    => (string) content('ambassadors', 'hero.lead'),
    'image'   => $heroImg['has'] ? ['src' => $heroImg['src'], 'alt' => $heroImg['alt'], 'focus' => '50% 40%'] : null,
    'actions' => $actions,
]) ?>

<?php /* ------------------------------------------------------------------ HIGHLIGHTS STRIP */ ?>
<?php $facts = $titled(content_list('ambassadors', 'facts.items')); if ($facts): section_begin('ambassadors', 'facts', ['class' => 'amb-facts', 'attrs' => ['aria-label' => 'Why become an ambassador']]); ?>
    <ul class="amb-facts__grid">
        <?php foreach ($facts as $f): ?>
            <li class="amb-fact" data-reveal>
                <span class="amb-fact__icon"><?= icon(safe_icon((string) ($f['icon'] ?? ''), 'check-circle'), 'icon--lg') ?></span>
                <span class="amb-fact__body"><strong><?= e($f['title']) ?></strong><?php if (trim((string) ($f['text'] ?? '')) !== ''): ?><span><?= e($f['text']) ?></span><?php endif; ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ WHAT IS AN AMBASSADOR */ ?>
<?php
$aboutImgs = [content_img('ambassadors', 'about.image'), content_img('ambassadors', 'about.image2')];
$aboutMedia = (bool) array_filter($aboutImgs, static fn(array $i): bool => $i['has']);
section_begin('ambassadors', 'about', ['class' => 'amb-about', 'labelledby' => 'amb-about-title']); ?>
    <div class="split split--start<?= $aboutMedia ? ' split--wide-media' : ' ia-split--solo' ?>">
        <?= partial('partials/photo-stack', ['images' => $aboutImgs]) ?>
        <div data-reveal>
            <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'about.eyebrow'), 'title' => (string) content('ambassadors', 'about.title'), 'id' => 'amb-about-title']) ?>
            <div class="prose"><?= content_html('ambassadors', 'about.body') ?></div>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ WHAT AMBASSADORS DO */ ?>
<?php $roles = $cardsOf('roles.items'); if ($roles): section_begin('ambassadors', 'roles', ['class' => 'section--tint amb-roles', 'labelledby' => 'amb-roles-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'roles.eyebrow'), 'title' => (string) content('ambassadors', 'roles.title'), 'lead' => (string) content('ambassadors', 'roles.intro'), 'align' => 'center', 'id' => 'amb-roles-title']) ?>
    <div class="amb-roles__grid">
        <?php foreach ($roles as $i => $r): ?>
            <article class="amb-role" data-reveal>
                <span class="amb-role__no" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <span class="amb-role__icon"><?= icon(safe_icon((string) ($r['icon'] ?? ''), 'check-circle'), 'icon--lg') ?></span>
                <h3 class="amb-role__title"><?= e($r['title']) ?></h3>
                <p class="amb-role__text"><?= e($r['text'] ?? '') ?></p>
            </article>
        <?php endforeach; ?>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ YEARLY PLEDGE (money, in kind, other) */ ?>
<?php
$pledges = $cardsOf('pledge.items');
$examples = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) content('ambassadors', 'pledge.examples')) ?: [])));
if ($pledges): section_begin('ambassadors', 'pledge', ['class' => 'amb-pledge', 'labelledby' => 'amb-pledge-title']); ?>
    <div class="amb-pledge__panel">
        <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'pledge.eyebrow'), 'title' => (string) content('ambassadors', 'pledge.title'), 'lead' => (string) content('ambassadors', 'pledge.intro'), 'align' => 'center', 'id' => 'amb-pledge-title']) ?>
        <div class="amb-pledge__grid">
            <?php foreach ($pledges as $p): ?>
                <article class="amb-pledge__card" data-reveal>
                    <span class="amb-pledge__icon"><?= icon(safe_icon((string) ($p['icon'] ?? ''), 'check-circle'), 'icon--lg') ?></span>
                    <h3><?= e($p['title']) ?></h3>
                    <p><?= e($p['text'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($examples): ?>
            <div class="amb-pledge__examples" data-reveal>
                <h3><?= e(content('ambassadors', 'pledge.examples_title')) ?></h3>
                <ul><?php foreach ($examples as $x): ?><li><?= icon('check', 'icon--sm') ?> <?= e($x) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ HOW IT WORKS */ ?>
<?php $steps = $titled(content_list('ambassadors', 'steps.items')); if ($steps): section_begin('ambassadors', 'steps', ['id' => 'how', 'class' => 'section--tint amb-steps', 'labelledby' => 'amb-steps-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'steps.eyebrow'), 'title' => (string) content('ambassadors', 'steps.title'), 'align' => 'center', 'id' => 'amb-steps-title']) ?>
    <ol class="amb-steps__list">
        <?php foreach ($steps as $i => $s): ?>
            <li class="amb-step" data-reveal>
                <span class="amb-step__num" aria-hidden="true"><?= (int) $i + 1 ?></span>
                <h3 class="amb-step__title"><?= e($s['title']) ?></h3>
                <p class="amb-step__text"><?= e($s['text'] ?? '') ?></p>
            </li>
        <?php endforeach; ?>
    </ol>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ WHAT AMBASSADORS RECEIVE */ ?>
<?php $benefits = $cardsOf('benefits.items'); if ($benefits): section_begin('ambassadors', 'benefits', ['class' => 'amb-benefits', 'labelledby' => 'amb-ben-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'benefits.eyebrow'), 'title' => (string) content('ambassadors', 'benefits.title'), 'align' => 'center', 'id' => 'amb-ben-title']) ?>
    <div class="ia-cards">
        <?php foreach ($benefits as $b): ?>
            <?= partial('partials/feature-card', ['icon' => (string) ($b['icon'] ?? ''), 'title' => (string) $b['title'], 'text' => (string) ($b['text'] ?? '')]) ?>
        <?php endforeach; ?>
    </div>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ ANYWHERE IN THE WORLD */ ?>
<?php section_begin('ambassadors', 'world', ['class' => 'amb-world', 'bleed' => true]); ?>
    <div class="amb-world__band">
        <div class="container amb-world__inner">
            <div class="amb-world__copy" data-reveal>
                <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'world.eyebrow'), 'title' => (string) content('ambassadors', 'world.title'), 'lead' => (string) content('ambassadors', 'world.text'), 'id' => 'amb-world-title', 'class' => 'amb-world__head']) ?>
                <?php if ($countryCount > 0): ?><p class="amb-world__count"><b><?= (int) $countryCount ?></b> <?= e(content('ambassadors', 'world.count_label')) ?></p><?php endif; ?>
            </div>
            <ul class="amb-world__regions" data-reveal>
                <?php foreach ($regions as $key => $label): ?><li><?= icon('globe', 'icon--sm') ?> <?= e($label) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ MEET OUR AMBASSADORS */ ?>
<?php if ($directory): section_begin('ambassadors', 'directory', ['class' => 'section--tint amb-directory', 'labelledby' => 'amb-dir-title']); ?>
    <?= partial('partials/section-head', ['eyebrow' => (string) content('ambassadors', 'directory.eyebrow'), 'title' => (string) content('ambassadors', 'directory.title'), 'lead' => (string) content('ambassadors', 'directory.intro'), 'align' => 'center', 'id' => 'amb-dir-title']) ?>
    <ul class="amb-people">
        <?php foreach ($directory as $p): $init = mb_strtoupper(mb_substr(trim((string) $p['name']), 0, 1)); ?>
            <li class="amb-person" data-reveal>
                <span class="amb-person__avatar" aria-hidden="true"><?= e($init) ?></span>
                <span class="amb-person__name"><?= e($p['name']) ?></span>
                <span class="amb-person__meta"><?= icon('map-pin', 'icon--sm') ?> <?= e($p['country']) ?></span>
                <?php $role = trim(implode(', ', array_filter([(string) ($p['occupation'] ?? ''), (string) ($p['organisation'] ?? '')]))); if ($role !== ''): ?><span class="amb-person__role"><?= e($role) ?></span><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php section_end(); endif; ?>

<?php /* ------------------------------------------------------------------ APPLICATION FORM (always on) */ ?>
<?php
$errTitle = (string) content('ambassadors', 'form.error_title');
$fieldMeta = [
    'name' => ['id' => 'f-name', 'label' => 'Your name'], 'email' => ['id' => 'f-email', 'label' => 'E-mail address'], 'phone' => ['id' => 'f-phone', 'label' => 'Phone or WhatsApp'],
    'region' => ['id' => 'f-region', 'label' => 'Region'], 'country' => ['id' => 'f-country', 'label' => 'Country'], 'roles' => ['id' => 'role-fundraise', 'label' => 'How you could help'],
    'pledge_types' => ['id' => 'pledge-money', 'label' => 'Your yearly pledge'], 'annual_amount' => ['id' => 'f-annual_amount', 'label' => 'Amount per year'], 'currency' => ['id' => 'f-currency', 'label' => 'Currency'],
    'inkind_items' => ['id' => 'inkind-computers', 'label' => 'In-kind items'], 'other_text' => ['id' => 'f-other_text', 'label' => 'Something else'],
    'motivation' => ['id' => 'f-motivation', 'label' => 'Why you would like to be an ambassador'], 'profile_url' => ['id' => 'f-profile_url', 'label' => 'Profile link'], 'consent' => ['id' => 'f-consent', 'label' => 'Consent'],
];
$oldRegion = (string) old('region', '');
$oldCountry = (string) old('country', '');
$oldRoles = (array) old('roles', []);
$oldPledges = (array) old('pledge_types', []);
$oldItems = (array) old('inkind_items', []);
$formEyebrow = trim((string) content('ambassadors', 'form.eyebrow'));
$formSub = trim((string) content('ambassadors', 'form.subtitle'));
$formText = trim((string) content('ambassadors', 'form.text'));
$this->start('scripts'); ?>
<script src="<?= e(asset('js/ambassadors.js')) ?>" defer></script>
<?php $this->end();
section_begin('ambassadors', 'form', ['id' => 'apply', 'class' => 'amb-apply', 'labelledby' => 'amb-apply-title']); ?>
    <div class="ia-panel ia-panel--split">
        <div class="ia-panel__intro" data-reveal>
            <?php if ($formEyebrow !== ''): ?><span class="eyebrow"><?= e($formEyebrow) ?></span><?php endif; ?>
            <h2 id="amb-apply-title"><?= highlight((string) content('ambassadors', 'form.title')) ?></h2>
            <?php if ($formSub !== ''): ?><p class="involvement-join__sub"><?= e($formSub) ?></p><?php endif; ?>
            <?php if ($formText !== ''): ?><div><?= nl2p($formText) ?></div><?php endif; ?>
            <ul class="amb-apply__points">
                <li><?= icon('globe', 'icon--sm') ?> Open to every country</li>
                <li><?= icon('heart', 'icon--sm') ?> You choose your pledge</li>
                <li><?= icon('check-circle', 'icon--sm') ?> No obligation until we have spoken</li>
            </ul>
        </div>
        <div data-reveal>
            <form class="form-card ia-form amb-form" method="post" action="<?= e(url('involvement/ambassadors')) ?>" novalidate data-amb-form>
                <?= partial('web/inner-a/_error_summary', ['title' => $errTitle, 'fields' => $fieldMeta, 'sid' => 'amb']) ?>
                <?= csrf_field() ?>
                <?= honeypot_field() ?>

                <fieldset class="amb-form__group">
                    <legend>1. About you</legend>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Your name', 'required' => true, 'autocomplete' => 'name', 'maxlength' => 150]) ?>
                        <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'autocomplete' => 'email', 'maxlength' => 190]) ?>
                        <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone or WhatsApp', 'type' => 'tel', 'autocomplete' => 'tel', 'maxlength' => 40, 'help' => 'Include the country code, for example +256.']) ?>
                        <?= partial('partials/form-field', ['name' => 'occupation', 'label' => 'What you do', 'maxlength' => 190, 'autocomplete' => 'organization-title', 'help' => 'Your profession or occupation.']) ?>
                        <?= partial('partials/form-field', ['name' => 'organisation', 'label' => 'Organisation (optional)', 'maxlength' => 190, 'autocomplete' => 'organization', 'help' => 'Your employer, school, congregation or group, if you would represent it.']) ?>
                        <?= partial('partials/form-field', ['name' => 'profile_url', 'label' => 'LinkedIn or website (optional)', 'type' => 'url', 'maxlength' => 300, 'autocomplete' => 'url']) ?>
                    </div>
                </fieldset>

                <fieldset class="amb-form__group">
                    <legend>2. Where in the world are you?</legend>
                    <div class="form-grid">
                        <div class="field<?= error('region') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-region">Region <span class="field__req" aria-hidden="true">*</span></label>
                            <select class="select" id="f-region" name="region" required data-amb-region<?= error('region') ? ' aria-invalid="true"' : '' ?>>
                                <option value="">Choose your region</option>
                                <?php foreach ($regions as $key => $label): ?><option value="<?= e($key) ?>"<?= $oldRegion === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                            </select>
                            <?php if (error('region')): ?><div class="field__error" role="alert"><?= e((string) error('region')) ?></div><?php endif; ?>
                        </div>
                        <div class="field<?= error('country') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-country">Country <span class="field__req" aria-hidden="true">*</span></label>
                            <select class="select" id="f-country" name="country" required data-amb-country<?= error('country') ? ' aria-invalid="true"' : '' ?>>
                                <option value="">Choose your country</option>
                                <?php foreach ($countries as $region => $list): ?>
                                    <optgroup label="<?= e($regions[$region] ?? $region) ?>" data-region="<?= e($region) ?>">
                                        <?php foreach ($list as $c): ?><option value="<?= e($c) ?>" data-region="<?= e($region) ?>"<?= $oldCountry === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <div class="field__help">Choose the region first and the list shows its countries.</div>
                            <?php if (error('country')): ?><div class="field__error" role="alert"><?= e((string) error('country')) ?></div><?php endif; ?>
                        </div>
                        <?= partial('partials/form-field', ['name' => 'city', 'label' => 'City or town (optional)', 'maxlength' => 120, 'autocomplete' => 'address-level2']) ?>
                    </div>
                </fieldset>

                <fieldset class="amb-form__group">
                    <legend>3. How could you help?</legend>
                    <div class="amb-checks<?= error('roles') ? ' amb-checks--error' : '' ?>">
                        <?php foreach (Amb::ROLES as $key => $label): ?>
                            <label class="check amb-check" for="role-<?= e($key) ?>"><input type="checkbox" id="role-<?= e($key) ?>" name="roles[]" value="<?= e($key) ?>"<?= in_array($key, $oldRoles, true) ? ' checked' : '' ?>><span><?= e($label) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (error('roles')): ?><div class="field__error" role="alert"><?= e((string) error('roles')) ?></div><?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'network', 'label' => 'Who could you reach? (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'help' => 'For example your company, a professional body, a church or mosque, a school, an alumni group or a diaspora community.']) ?>
                </fieldset>

                <fieldset class="amb-form__group amb-form__group--pledge">
                    <legend>4. What can you raise or give each year?</legend>
                    <p class="amb-form__note">Tick everything that applies. This is a good-faith intention, not a contract.</p>
                    <div class="amb-pledgebox<?= error('pledge_types') ? ' amb-pledgebox--error' : '' ?>">
                        <?php foreach (Amb::PLEDGES as $key => $label): ?>
                            <label class="amb-pledgebox__opt" for="pledge-<?= e($key) ?>">
                                <input type="checkbox" id="pledge-<?= e($key) ?>" name="pledge_types[]" value="<?= e($key) ?>" data-amb-pledge="<?= e($key) ?>"<?= in_array($key, $oldPledges, true) ? ' checked' : '' ?>>
                                <span class="amb-pledgebox__label"><?= icon(['money' => 'banknote', 'inkind' => 'laptop', 'other' => 'star'][$key] ?? 'star', 'icon--lg') ?><b><?= e($label) ?></b></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (error('pledge_types')): ?><div class="field__error" role="alert"><?= e((string) error('pledge_types')) ?></div><?php endif; ?>

                    <div class="amb-pledge-panel" data-amb-panel="money">
                        <h3>Money</h3>
                        <div class="form-grid">
                            <?= partial('partials/form-field', ['name' => 'annual_amount', 'label' => 'How much could you raise or give in a year?', 'type' => 'text', 'attrs' => ['inputmode' => 'decimal'], 'maxlength' => 20, 'placeholder' => 'For example 2,500', 'help' => 'The total you could lobby for or give yourself, in one year.']) ?>
                            <?= partial('partials/form-field', ['name' => 'currency', 'label' => 'Currency', 'type' => 'select', 'options' => $currencies, 'empty' => 'Choose a currency']) ?>
                        </div>
                    </div>

                    <div class="amb-pledge-panel" data-amb-panel="inkind">
                        <h3>In-kind support</h3>
                        <div class="amb-checks<?= error('inkind_items') ? ' amb-checks--error' : '' ?>">
                            <?php foreach (Amb::INKIND as $key => $label): ?>
                                <label class="check amb-check" for="inkind-<?= e($key) ?>"><input type="checkbox" id="inkind-<?= e($key) ?>" name="inkind_items[]" value="<?= e($key) ?>"<?= in_array($key, $oldItems, true) ? ' checked' : '' ?>><span><?= e($label) ?></span></label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (error('inkind_items')): ?><div class="field__error" role="alert"><?= e((string) error('inkind_items')) ?></div><?php endif; ?>
                        <?= partial('partials/form-field', ['name' => 'inkind_notes', 'label' => 'Quantity, condition and where the items are', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'help' => 'For example: 20 refurbished laptops in Toronto, about USD 6,000 in value.']) ?>
                    </div>

                    <div class="amb-pledge-panel" data-amb-panel="other">
                        <h3>Something else</h3>
                        <?= partial('partials/form-field', ['name' => 'other_text', 'label' => 'What else could you lobby for or give?', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 3000, 'help' => 'Money, services, scholarships, training, venues, software, flights: write it in your own words.']) ?>
                    </div>
                </fieldset>

                <fieldset class="amb-form__group">
                    <legend>5. A little more about you</legend>
                    <div class="form-grid">
                        <?= partial('partials/form-field', ['name' => 'availability', 'label' => 'How much time could you give?', 'type' => 'select', 'options' => Amb::AVAILABILITY, 'empty' => 'Choose one']) ?>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'motivation', 'label' => 'Why would you like to be an ambassador?', 'type' => 'textarea', 'rows' => 5, 'required' => true, 'maxlength' => 3000, 'class' => 'field--full']) ?>
                </fieldset>

                <div class="amb-form__consent">
                    <?= partial('partials/form-field', ['name' => 'consent', 'label' => (string) content('ambassadors', 'form.consent_label'), 'type' => 'checkbox', 'required' => true]) ?>
                    <?= partial('partials/form-field', ['name' => 'public_ok', 'label' => (string) content('ambassadors', 'form.public_label'), 'type' => 'checkbox']) ?>
                </div>
                <div class="form-actions">
                    <button class="btn btn--cta btn--caps btn--lg" type="submit" data-loading-text="<?= e(content('ambassadors', 'form.sending_label')) ?>"><?= e(content('ambassadors', 'form.submit_label')) ?></button>
                    <span class="form-note">Fields marked * are required. <?= e(content('ambassadors', 'form.privacy_note')) ?></span>
                </div>
            </form>
        </div>
    </div>
<?php section_end(); ?>

<?php /* ------------------------------------------------------------------ FAQ with photo */ ?>
<?php if ($faqs): ?>
<?php section_begin('ambassadors', 'faq', ['class' => 'section--tint amb-faq', 'attrs' => ['aria-label' => strip_highlight((string) content('ambassadors', 'faq.title'))]]); ?>
    <?= partial('partials/faq-split', [
        'faqs'    => $faqs,
        'image'   => content_img('ambassadors', 'faq.image'),
        'eyebrow' => (string) content('ambassadors', 'faq.eyebrow'),
        'title'   => (string) content('ambassadors', 'faq.title'),
        'prefix'  => 'amb-faq',
    ]) ?>
<?php section_end(); ?>
<?php endif; ?>

<?= partial('partials/cta-donate') ?>
