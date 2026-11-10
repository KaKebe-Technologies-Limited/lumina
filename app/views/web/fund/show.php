<?php
/**
 * ONE PROJECT (/fund-a-project/{slug}). Vars: $p (the project), $others (the other projects), $methods, $currencies, $gets (what every partner receives).
 * Order: cover > title + key figures > the need and our response > what we will deliver > why donors choose it > the budget > CHOOSE HOW TO GIVE (packages, one contribution, any amount + the pledge form)
 * > what partners receive > questions > the other projects. The price of a fixed choice is calculated on the server again when the pledge is sent.
 */
$this->start('scripts'); ?><script src="<?= e(asset('js/fund.js')) ?>" defer></script><?php $this->end();
$coming = $p['status'] !== 'proposal';
$ugxFmt = static fn(float|int $v): string => 'UGX ' . number_format((float) $v);
$usdFmt = static fn(float|int $v): string => 'USD ' . number_format((float) $v);
$cover = asset('img/projects/' . $p['cover'] . '.jpg');
$proposalMail = 'mailto:' . (string) setting('email_info', setting('email_primary', '')) . '?subject=' . rawurlencode('Full proposal: ' . $p['short']);
?>
<section class="fund-hero">
    <div class="container">
        <?= partial('partials/breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []]) ?>
        <figure class="fund-hero__cover"><img src="<?= e($cover) ?>" alt="<?= e($p['title']) ?>: <?= e($p['tagline']) ?>" width="1600" height="900" fetchpriority="high"></figure>
        <div class="fund-hero__body">
            <span class="eyebrow"><?= e($p['pillar']) ?></span>
            <h1 class="fund-hero__title"><?= e($p['title']) ?></h1>
            <p class="fund-hero__tagline"><?= e($p['tagline']) ?></p>
            <p class="fund-hero__meta"><?= icon('map-pin', 'icon--sm') ?> <?= e($p['where']) ?> &middot; <?= icon('calendar', 'icon--sm') ?> <?= e($p['period']) ?></p>
            <div class="fund-hero__actions">
                <a class="btn btn--cta btn--caps" href="#give"><?= icon('heart', 'icon--sm') ?> <?= $coming ? 'Register your interest' : 'Choose how to give' ?></a>
                <?php if (!$coming): ?><a class="btn btn--photo btn--caps" href="<?= e($proposalMail) ?>"><?= icon('mail', 'icon--sm') ?> Request the full proposal</a><?php endif; ?>
            </div>
            <p class="fund-hero__note">A proposed project seeking funding. Choose what you would like to give, then press Give now.</p>
        </div>
    </div>
</section>

<?php if ($coming): ?>
    <section class="section section--tint">
        <div class="container container--narrow">
            <div class="fund-soon">
                <h2>The full proposal is being prepared</h2>
                <p><?= e($p['summary']) ?> We are writing the proposal and budget now. If you would like to support this work, or hear first when it is ready, tell us below: we will contact you with the details.</p>
            </div>
        </div>
    </section>
<?php else: ?>

<section class="fund-figures" aria-label="The project in numbers">
    <div class="container">
        <ul class="fund-figures__list">
            <?php foreach ($p['figures'] as [$v, $l]): ?>
                <li class="fund-figure"><b><?= e($v) ?></b><span><?= e($l) ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container fund-cols">
        <div class="fund-cols__main">
            <span class="eyebrow">The need</span>
            <h2 class="fund-h2">Why this project matters</h2>
            <p class="fund-lead"><?= e($p['need']) ?></p>
            <h3 class="fund-h3">Our response</h3>
            <p><?= e($p['response']) ?></p>
        </div>
        <aside class="fund-cols__side">
            <div class="fund-invest">
                <span class="fund-invest__label">The investment</span>
                <strong class="fund-invest__ugx"><?= e($ugxFmt($p['budget']['ugx'])) ?></strong>
                <span class="fund-invest__usd">about <?= e($usdFmt($p['budget']['usd'])) ?> over 36 months</span>
                <p class="fund-invest__note">Start small and scale with confidence: a Year 1 package costs <strong><?= e($usdFmt($p['packages'][1]['usd'])) ?></strong>, or sponsor a single contribution from the list below.</p>
                <a class="btn btn--cta btn--caps btn--block" href="#give">Give to this project</a>
            </div>
        </aside>
    </div>
</section>

<section class="section section--tint" aria-labelledby="fund-deliver">
    <div class="container">
        <header class="section-head"><span class="eyebrow">What we will deliver</span><h2 class="section-head__title" id="fund-deliver">What your support makes possible</h2></header>
        <ul class="fund-deliver">
            <?php foreach ($p['deliver'] as $d): ?><li><?= icon('check-circle', 'icon--sm') ?><span><?= e($d) ?></span></li><?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="fund-why">
    <div class="container">
        <header class="section-head"><span class="eyebrow">Why donors choose it</span><h2 class="section-head__title" id="fund-why">Built to last, and accountable</h2></header>
        <div class="fund-why">
            <?php foreach ($p['why'] as $i => [$t, $x]): ?>
                <article class="fund-why__card"><span class="fund-why__n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($t) ?></h3><p><?= e($x) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--navy" aria-labelledby="fund-budget">
    <div class="container">
        <header class="section-head section-head--on-dark"><span class="eyebrow">Where the money goes</span><h2 class="section-head__title" id="fund-budget">A budget built line by line</h2>
            <p class="section-head__lead">Total request <?= e($ugxFmt($p['budget']['ugx'])) ?> (about <?= e($usdFmt($p['budget']['usd'])) ?>), <?= e($p['budget']['note']) ?>. Every figure comes from our detailed budget, which funders receive with the full proposal.</p></header>
        <ul class="fund-bars">
            <?php foreach ($p['budget']['bars'] as [$label, $pct]): ?>
                <li class="fund-bar"><span class="fund-bar__label"><?= e($label) ?></span><span class="fund-bar__track" role="img" aria-label="<?= e($label) ?>: <?= e(number_format($pct, 1)) ?> percent"><i style="width: <?= e((string) min(100, max(1, $pct))) ?>%"></i></span><b class="fund-bar__pct"><?= e(number_format($pct, 1)) ?>%</b></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php endif; ?>

<section class="section section--tint" id="give" aria-labelledby="fund-give">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow"><?= $coming ? 'Register your interest' : 'Choose how to give' ?></span><h2 class="section-head__title" id="fund-give"><?= $coming ? 'Support ' . e($p['short']) : 'Fund the whole project, or pick one contribution' ?></h2>
            <p class="section-head__lead"><?= $coming ? 'Tell us how much you would like to give and we will be in touch.' : 'Every choice below is real and priced. Pick one, add your details and press Give now: the amount is filled in for you.' ?></p></header>

        <form class="fund-form" method="post" action="<?= e(url('fund-a-project/' . $p['slug'] . '/pledge')) ?>" data-fund-form data-rate="3750" novalidate>
            <?= csrf_field() ?><?= honeypot_field() ?>
            <div class="fund-form__choices">
                <?php if (error('choice')): ?><div class="alert alert--error" role="alert"><?= e((string) error('choice')) ?></div><?php endif; ?>

                <?php if (!$coming): ?>
                <fieldset class="fund-set">
                    <legend class="fund-set__legend">1. Choose a package</legend>
                    <div class="fund-cards">
                        <?php foreach ($p['packages'] as $k): $val = 'package:' . $k['key']; $isCo = $k['key'] === 'cofund'; ?>
                            <label class="fund-card fund-card--package">
                                <input type="radio" name="choice" value="<?= e($val) ?>" data-title="<?= e($k['title']) ?>"<?= $isCo ? ' data-free data-min-usd="' . (int) $k['min_usd'] . '"' : ' data-ugx="' . (int) $k['ugx'] . '" data-usd="' . (int) $k['usd'] . '"' ?><?= old('choice') === $val ? ' checked' : '' ?>>
                                <span class="fund-card__body">
                                    <b class="fund-card__title"><?= e($k['title']) ?></b>
                                    <span class="fund-card__price"><?php if ($isCo): ?>From <?= e($usdFmt($k['min_usd'])) ?><?php else: ?><?= e($ugxFmt($k['ugx'])) ?> <small>(about <?= e($usdFmt($k['usd'])) ?>)</small><?php endif; ?></span>
                                    <span class="fund-card__text"><?= e($k['text']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset class="fund-set">
                    <legend class="fund-set__legend">2. Or sponsor one contribution</legend>
                    <p class="fund-set__hint">Pick one and choose how many (for example three scholars, or 500 trees).</p>
                    <div class="fund-cards fund-cards--items">
                        <?php foreach ($p['items'] as $i): $val = 'item:' . $i['key']; ?>
                            <label class="fund-card fund-card--item">
                                <input type="radio" name="choice" value="<?= e($val) ?>" data-title="<?= e($i['title']) ?>" data-ugx="<?= (int) $i['ugx'] ?>" data-usd="<?= (int) $i['usd'] ?>" data-item<?= old('choice') === $val ? ' checked' : '' ?>>
                                <span class="fund-card__body">
                                    <b class="fund-card__title"><?= e($i['title']) ?></b>
                                    <span class="fund-card__price"><?= e($ugxFmt($i['ugx'])) ?> <small>(<?= e($usdFmt($i['usd'])) ?>)</small></span>
                                    <span class="fund-card__text"><?= e($i['impact']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <?php endif; ?>

                <fieldset class="fund-set">
                    <legend class="fund-set__legend"><?= $coming ? '' : '3. Or give any amount' ?><?= $coming ? 'Your gift' : '' ?></legend>
                    <label class="fund-card fund-card--any">
                        <input type="radio" name="choice" value="any" data-title="A gift of my choice" data-free<?= $coming || old('choice') === 'any' ? ' checked' : '' ?>>
                        <span class="fund-card__body"><b class="fund-card__title">Any amount</b><span class="fund-card__text">Every gift counts. Type what you would like to give.</span></span>
                    </label>
                </fieldset>
            </div>

            <aside class="fund-form__side" aria-live="polite">
                <div class="fund-summary" data-fund-summary>
                    <h3 class="fund-summary__title">Your gift</h3>
                    <p class="fund-summary__chosen" data-fund-chosen><?= $coming ? 'A gift of my choice' : 'Nothing chosen yet. Pick a package or a contribution.' ?></p>
                    <div class="fund-summary__row" data-fund-qty-row hidden>
                        <label for="fund-qty">How many?</label>
                        <input class="input" id="fund-qty" type="number" name="qty" min="1" max="500" value="<?= e((string) (old('qty') ?: 1)) ?>" inputmode="numeric">
                    </div>
                    <div class="fund-summary__row" data-fund-amount-row hidden>
                        <label for="fund-amount">Amount</label>
                        <input class="input<?= error('amount') ? ' is-invalid' : '' ?>" id="fund-amount" type="text" name="amount" inputmode="decimal" value="<?= e((string) old('amount')) ?>" placeholder="for example 50000" autocomplete="off">
                    </div>
                    <?php if (error('amount')): ?><div class="field__error" role="alert"><?= e((string) error('amount')) ?></div><?php endif; ?>
                    <div class="fund-summary__row">
                        <label for="fund-currency">Currency</label>
                        <select class="select" id="fund-currency" name="currency" data-fund-currency>
                            <?php foreach ($currencies as $c): ?><option value="<?= e($c) ?>"<?= (old('currency') ?: 'UGX') === $c ? ' selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (error('currency')): ?><div class="field__error" role="alert"><?= e((string) error('currency')) ?></div><?php endif; ?>
                    <p class="fund-summary__total" data-fund-total hidden>Total: <strong data-fund-total-value></strong></p>
                    <p class="fund-summary__big" data-fund-big hidden role="status">One mobile-money payment can be up to UGX 5,000,000. For a larger gift, press Give now and we will contact you to arrange the payment.</p>
                </div>

                <div class="fund-details">
                    <h3 class="fund-summary__title">Your details</h3>
                    <?= partial('partials/form-field', ['name' => 'donor_name', 'label' => 'Your name', 'required' => true, 'maxlength' => 150, 'autocomplete' => 'name']) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
                    <?= partial('partials/form-field', ['name' => 'donor_phone', 'label' => 'Phone or WhatsApp (optional)', 'type' => 'tel', 'maxlength' => 40, 'autocomplete' => 'tel']) ?>
                    <?php if (isset($methods['mobile_now'])): ?><?= partial('partials/pay-banner', ['compact' => true, 'text' => 'You can pay now with MTN or Airtel']) ?><?php endif; ?>
                    <?php if (count($methods) > 1): ?>
                    <?= partial('partials/form-field', ['name' => 'method', 'label' => 'How would you like to give?', 'type' => 'select', 'required' => true, 'options' => $methods, 'value' => old('method') ?: (isset($methods['mobile_now']) ? 'mobile_now' : array_key_first($methods))]) ?>
                    <?php else: ?><input type="hidden" name="method" value="<?= e((string) array_key_first($methods)) ?>"><?php endif; ?>
                    <?= partial('partials/form-field', ['name' => 'message', 'label' => 'Message (optional)', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 1500, 'placeholder' => 'Tell us anything you would like us to know, or how you would like to be recognised.']) ?>
                    <?= partial('partials/form-field', ['name' => 'is_anonymous', 'label' => 'I would like my gift to stay anonymous', 'type' => 'checkbox']) ?>
                    <button class="btn btn--cta btn--caps btn--block" type="submit"><?= icon('heart', 'icon--sm') ?> <span data-fund-give>Give now</span></button>
                    <p class="fund-details__note"><?= isset($methods['mobile_now']) ? 'Press Give now to pay with MTN or Airtel mobile money. You approve the payment on your phone.' : 'Press Give now and we will contact you with the details to complete your gift.' ?></p>
                </div>
            </aside>
        </form>
    </div>
</section>

<?php if (!$coming): ?>
<section class="section" aria-labelledby="fund-gets">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow">What our funding partners receive</span><h2 class="section-head__title" id="fund-gets">You will always know where your gift went</h2></header>
        <div class="fund-gets">
            <?php foreach ($gets as $g): ?>
                <article class="fund-gets__card"><span class="fund-gets__icon"><?= icon($g['icon'], 'icon--lg') ?></span><h3><?= e($g['title']) ?></h3><p><?= e($g['text']) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($p['faq'] !== []): ?>
<section class="section section--tint" aria-labelledby="fund-faq">
    <div class="container container--narrow">
        <header class="section-head section-head--center"><span class="eyebrow">Questions funders often ask</span><h2 class="section-head__title" id="fund-faq">Good questions, honest answers</h2></header>
        <div class="fund-faq">
            <?php foreach ($p['faq'] as [$q, $a]): ?>
                <details class="fund-faq__item"><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details>
            <?php endforeach; ?>
        </div>
        <p class="fund-faq__more">Want the full proposal and the detailed budget? <a href="<?= e($proposalMail) ?>">Ask us for them</a>, or call <a href="<?= e(phone_href((string) setting('phone_1', ''))) ?>"><?= e((string) setting('phone_1', '')) ?></a>.</p>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<section class="section" aria-labelledby="fund-more">
    <div class="container">
        <header class="section-head section-head--center"><span class="eyebrow">More ways to change lives</span><h2 class="section-head__title" id="fund-more">Other projects you can fund</h2></header>
        <ul class="fund-grid fund-grid--small">
            <?php foreach ($others as $o): ?>
                <li><?= partial('partials/fund-card', ['p' => $o]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
