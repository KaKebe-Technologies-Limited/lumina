<?php
/**
 * Admin > Social media > Profile links (ability social.manage). OWNER: ADMIN agent.
 * One row per platform: icon preview, address box with a live check, up / down arrows (and drag) for the order, where-to-show switches, a live footer preview.
 * Vars: $platforms (SocialProfiles::PLATFORMS), $values (platform => saved address), $order (platform keys in order), $shows (footer|contact|newsletter|maintenance => bool).
 * Form fields (unchanged contract): url_<platform>, order_<platform> (position, 1 = first), show_footer, show_contact, show_newsletter, show_maintenance. POST admin/social.
 * Works without JavaScript: the position boxes are visible and the arrows / live check appear only when assets/js/admin-social.js runs.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-social.js')) ?>" defer></script>
<?php $this->end();
$places = [
    'footer'      => ['In the website footer', 'The icon row at the bottom of every page.'],
    'contact'     => ['On the Contact page', 'Under the contact details.'],
    'newsletter'  => ['In newsletter e-mails', 'A "Follow us" line above the small print.'],
    'maintenance' => ['On the maintenance page', 'Shown to visitors while the site is switched off.'],
];
$filled = 0;
foreach ($values as $v) { if ($v !== '') { $filled++; } }
?>
<div class="asoc">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Social media profile links',
        'subtitle' => 'The addresses of your public accounts. They appear where you choose below and also help search engines connect these accounts to your website.',
        'actions'  => [
            ['label' => 'Auto-share channels', 'url' => url('admin/social/channels'), 'class' => 'btn--outline', 'icon' => 'send'],
            ['label' => 'Post templates', 'url' => url('admin/social/templates'), 'class' => 'btn--outline', 'icon' => 'edit'],
        ],
    ]) ?>

    <div class="asoc-intro" role="note">
        <?= icon('info') ?>
        <p>These are only <strong>links to your pages</strong>, so visitors can find you. They do not post anything. To have every newsletter issue posted to your accounts automatically when it is published,
            set up the <a href="<?= e(url('admin/social/channels')) ?>">auto-share channels</a> (each one needs its own keys from the platform).</p>
    </div>

    <form method="post" action="<?= e(url('admin/social')) ?>" novalidate data-unsaved-guard data-asoc-profiles class="asoc-prof">
        <?= csrf_field() ?>
        <section class="card" aria-labelledby="sp-links">
            <div class="card__header">
                <h2 class="card__title" id="sp-links">Your accounts</h2>
                <span class="asoc-count" data-asoc-prof-count><?= (int) $filled ?> of <?= count($platforms) ?> added</span>
            </div>
            <div class="card__body">
                <p class="asoc-prof__tip">Paste the full web address of each page (it must start with <code>https://</code>). A bare name such as <code>@yourname</code> is turned into an address for you.
                    Leave a box empty to hide that platform. <span class="asoc-prof__js-tip" hidden>Use the arrows (or drag the handle) to set the order the icons appear in.</span></p>
                <ol class="asoc-prof__list" data-asoc-prof-list>
                    <?php $n = 0; foreach ($order as $key): $p = $platforms[$key]; $n++;
                        $fid = 'f-url_' . $key; $err = error('url_' . $key); $val = (string) old('url_' . $key, $values[$key] ?? ''); ?>
                        <li class="asoc-prof__row<?= $err ? ' is-bad' : '' ?>" data-key="<?= e($key) ?>" data-name="<?= e($p['name']) ?>" data-hosts="<?= e(implode(',', $p['hosts'])) ?>" data-handle="<?= e($p['handle']) ?>" data-example="<?= e($p['example']) ?>">
                            <span class="asoc-prof__grip" data-grip hidden aria-hidden="true" title="Drag to reorder"><?= icon('grip', 'icon--sm') ?></span>
                            <span class="asoc-prof__icon asoc-prof__icon--<?= e($key) ?>" aria-hidden="true"><?= icon($p['icon']) ?></span>
                            <div class="asoc-prof__main">
                                <label class="field__label" for="<?= e($fid) ?>"><?= e($p['name']) ?></label>
                                <input class="input" type="text" inputmode="url" id="<?= e($fid) ?>" name="url_<?= e($key) ?>" value="<?= e($val) ?>" maxlength="300" autocomplete="off" spellcheck="false"
                                       placeholder="<?= e($p['example']) ?>" data-asoc-url<?= $err ? ' aria-invalid="true" aria-describedby="' . e($fid) . '-error"' : ' aria-describedby="' . e($fid) . '-check"' ?>>
                                <?php if ($err): ?><div class="field__error" id="<?= e($fid) ?>-error" role="alert"><?= e($err) ?></div><?php endif; ?>
                                <div class="asoc-prof__check" id="<?= e($fid) ?>-check" data-asoc-check aria-live="polite"></div>
                            </div>
                            <div class="asoc-prof__order">
                                <label class="asoc-prof__posbox" for="f-order_<?= e($key) ?>"><span class="visually-hidden"><?= e($p['label']) ?>: position</span>
                                    <input class="input" type="number" min="1" max="99" step="1" id="f-order_<?= e($key) ?>" name="order_<?= e($key) ?>" value="<?= (int) old('order_' . $key, (string) $n) ?>" data-asoc-pos></label>
                                <div class="asoc-prof__arrows" data-arrows hidden>
                                    <button type="button" class="btn btn--outline btn--sm asoc-iconbtn" data-move="up" aria-label="Move <?= e($p['name']) ?> up"><?= icon('arrow-up', 'icon--sm') ?></button>
                                    <button type="button" class="btn btn--outline btn--sm asoc-iconbtn" data-move="down" aria-label="Move <?= e($p['name']) ?> down"><?= icon('arrow-down', 'icon--sm') ?></button>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <p class="visually-hidden" role="status" data-asoc-announce></p>
            </div>
        </section>

        <section class="card" aria-labelledby="sp-where">
            <div class="card__header"><h2 class="card__title" id="sp-where">Where the links are shown</h2></div>
            <div class="card__body">
                <ul class="asoc-where">
                    <?php foreach ($places as $k => [$label, $hint]): $on = old('show_' . $k, $shows[$k] ? '1' : '') !== '' && old('show_' . $k, $shows[$k] ? '1' : '') !== '0'; ?>
                        <li class="asoc-where__item">
                            <label class="switch"><input type="checkbox" name="show_<?= e($k) ?>" value="1"<?= $on ? ' checked' : '' ?> data-asoc-show="<?= e($k) ?>"><span class="switch__track"></span><span class="switch__text"><?= e($label) ?></span></label>
                            <span class="asoc-where__hint"><?= e($hint) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p class="field__help">Search engines are always told about these accounts (the "sameAs" information in the page data), whatever you choose above.</p>
            </div>
        </section>

        <section class="card" aria-labelledby="sp-prev">
            <div class="card__header"><h2 class="card__title" id="sp-prev">Preview</h2></div>
            <div class="card__body">
                <p class="field__help">How the icon row looks in the footer, in your order. It updates as you type.</p>
                <div class="asoc-prof__preview" data-asoc-preview aria-live="polite"><span class="asoc-prof__empty">No account added yet.</span></div>
            </div>
        </section>

        <div class="form-actions"><button class="btn btn--primary" type="submit">Save links</button></div>
    </form>
</div>
