<?php
/**
 * Admin > Social media > Post templates (ability social.manage). OWNER: ADMIN agent.
 * One template per platform with placeholder buttons, a LIVE counter (as the platform counts: a link counts 23 on X) and a live preview built by the server
 * (POST admin/social/templates/preview, the same builder that writes the real captions). Works without JavaScript.
 * Vars: $rows (channel|tiktok => ['meta','template','default','custom','default_on','sample' => CaptionBuilder::build result]), $placeholders, $hashtags, $utm.
 * Fields: tpl_<channel>, default_on_<channel>, hashtags, utm. POST admin/social/templates.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-social.js')) ?>" defer></script>
<?php $this->end();
$phHelp = [
    'title' => 'the issue title', 'summary' => 'the short summary', 'url' => 'the link to the issue (with tracking tags when that option is on)', 'issue_number' => 'just the number, like 12',
    'date' => 'like 20 October 2026', 'hashtags' => 'the default hashtags below', 'site' => 'the name of your organisation',
];
?>
<div class="asoc">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Post templates',
        'subtitle' => 'The wording each platform starts with when an issue is shared. You can still change the caption of every single issue before you publish it.',
        'actions'  => [['label' => 'Auto-share channels', 'url' => url('admin/social/channels'), 'class' => 'btn--outline', 'icon' => 'send']],
    ]) ?>
    <form method="post" action="<?= e(url('admin/social/templates')) ?>" novalidate data-unsaved-guard data-asoc-templates data-preview-url="<?= e(url('admin/social/templates/preview')) ?>">
        <p class="visually-hidden" role="status" aria-live="polite" data-asoc-announce-tpl></p>
        <?= csrf_field() ?>
        <section class="card" aria-labelledby="tp-ph">
            <div class="card__header"><h2 class="card__title" id="tp-ph">Placeholders, hashtags and tracking</h2></div>
            <div class="card__body">
                <p>Type these exactly like this, with curly brackets, anywhere in a template. They are replaced when the issue is shared:</p>
                <dl class="asoc-ph">
                    <?php foreach ($placeholders as $ph): ?><div><dt><code>{<?= e($ph) ?>}</code></dt><dd><?= e($phHelp[$ph] ?? '') ?></dd></div><?php endforeach; ?>
                </dl>
                <p class="field__help">When a text is too long for a platform, the summary is shortened first, then the hashtags are left out, then the title. A caption you type yourself for one issue is never shortened silently.</p>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'hashtags', 'label' => 'Default hashtags', 'value' => (string) old('hashtags', $hashtags), 'maxlength' => 600, 'help' => 'Separated by spaces. Letters, numbers and underscores only.']) ?>
                    <div class="field">
                        <label class="check"><input type="checkbox" name="utm" value="1"<?= $utm ? ' checked' : '' ?>><span>Add tracking tags to the link <small>(<code>utm_source=&lt;platform&gt;&amp;utm_medium=social&amp;utm_campaign=newsletter</code>)</small></span></label>
                    </div>
                </div>
            </div>
        </section>

        <?php foreach ($rows as $ch => $r): $m = $r['meta']; $err = error('tpl_' . $ch); ?>
            <section class="card asoc-tpl" aria-labelledby="tp-<?= e($ch) ?>" data-asoc-tpl="<?= e($ch) ?>" data-default="<?= e($r['default']) ?>" data-limit="<?= (int) $m['limit'] ?>" data-soft="<?= (int) ($m['soft'] ?? 0) ?>">
                <div class="card__header">
                    <h2 class="card__title" id="tp-<?= e($ch) ?>"><span class="asoc-chicon asoc-chicon--<?= e($ch) ?>"><?= icon($m['icon']) ?></span> <?= e($m['label']) ?><?= $ch === 'tiktok' ? ' <small>(for the Share kit and webhooks)</small>' : '' ?></h2>
                    <span class="asoc-meter" data-asoc-meter><span class="asoc-meter__bar"><span class="asoc-meter__fill" data-fill></span></span><span class="asoc-meter__text" id="tp-count-<?= e($ch) ?>" data-asoc-limit="<?= (int) $m['limit'] ?>" data-asoc-channel="<?= e($ch) ?>"><?= (int) $r['sample']['length'] ?> / <?= (int) $m['limit'] ?></span></span>
                </div>
                <div class="card__body">
                    <p class="asoc-tpl__rule"><?= $m['count'] === 'x' ? 'X counts every link as 23 characters, whatever its length.' : ($m['links'] === 'none' ? 'Links in a caption are not clickable here, so the built-in text says "link in bio".' : 'Limit ' . (int) $m['limit'] . ' characters.') ?></p>
                    <div class="field<?= $err ? ' field--error' : '' ?>">
                        <label class="field__label" for="f-tpl_<?= e($ch) ?>">Template</label>
                        <textarea class="textarea" rows="6" id="f-tpl_<?= e($ch) ?>" name="tpl_<?= e($ch) ?>" maxlength="2000" data-asoc-template="<?= e($ch) ?>" aria-describedby="tp-count-<?= e($ch) ?>"<?= $err ? ' aria-invalid="true"' : '' ?>><?= e((string) old('tpl_' . $ch, $r['template'])) ?></textarea>
                        <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
                        <div class="field__help"><?= $r['custom'] ? 'This is your own template.' : 'This is the built-in template.' ?></div>
                    </div>
                    <div class="asoc-tpl__tools" data-asoc-tools hidden>
                        <span class="asoc-tpl__tools-label">Insert:</span>
                        <?php foreach ($placeholders as $ph): ?><button type="button" class="asoc-ph-btn" data-insert="{<?= e($ph) ?>}">{<?= e($ph) ?>}</button><?php endforeach; ?>
                        <button type="button" class="btn btn--ghost btn--sm" data-reset><?= icon('refresh', 'icon--sm') ?> Use the built-in template</button>
                    </div>
                    <?php if ($r['default_on'] !== null): ?>
                        <div class="asoc-tpl__default"><label class="switch"><input type="checkbox" name="default_on_<?= e($ch) ?>" value="1"<?= $r['default_on'] ? ' checked' : '' ?>><span class="switch__track"></span><span class="switch__text">Tick this channel by default for new issues</span></label></div>
                    <?php endif; ?>
                    <p class="field__help asoc-tpl__sample-label">Preview with <span data-asoc-sample-for>a sample issue</span> (<span data-asoc-sample-count><?= (int) $r['sample']['length'] ?> of <?= (int) $r['sample']['limit'] ?></span> characters):</p>
                    <pre class="asoc-sample" data-asoc-sample="<?= e($ch) ?>"><?= e($r['sample']['text']) ?></pre>
                    <p class="asoc-tpl__warn field__error" data-asoc-warn role="status" hidden></p>
                </div>
            </section>
        <?php endforeach; ?>
        <div class="form-actions"><button class="btn btn--primary" type="submit">Save templates</button></div>
    </form>

    <section class="card asoc-design" id="design" aria-labelledby="tp-design">
        <div class="card__header"><h2 class="card__title" id="tp-design"><?= icon('image') ?> Share picture design</h2></div>
        <div class="card__body">
            <p>The pictures that go with a shared issue (square for Instagram, Facebook, X and Telegram, tall for TikTok, wide for link previews) are drawn from the issue cover photo, or from these colours when the issue has no cover. They are drawn by the website itself: no outside service.
                <?= $canRender ? '' : '<strong>This server cannot draw pictures (the GD image extension is missing), so the cover photo or the logo is used as it is and these colours have no effect.</strong>' ?></p>
            <div class="asoc-design__grid">
                <form method="post" action="<?= e(url('admin/social/design')) ?>" novalidate data-unsaved-guard class="asoc-design__form">
                    <?= csrf_field() ?>
                    <div class="asoc-design__colours">
                        <?php foreach (['bg' => 'Background (when there is no cover photo)', 'bg_dark' => 'Dark shade at the bottom', 'accent' => 'Accent (gold)', 'accent2' => 'Second accent (orange)', 'text' => 'Text'] as $k => $label): $err = error($k); ?>
                            <div class="field<?= $err ? ' field--error' : '' ?>">
                                <label class="field__label" for="f-design-<?= e($k) ?>"><?= e($label) ?></label>
                                <div class="asoc-design__pick"><input class="asoc-design__swatch" type="color" id="f-design-<?= e($k) ?>" name="<?= e($k) ?>" value="<?= e((string) old($k, $design[$k])) ?>"<?= $err ? ' aria-invalid="true"' : '' ?>><code><?= e((string) old($k, $design[$k])) ?></code></div>
                                <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'tagline', 'label' => 'Tagline on the picture', 'value' => (string) old('tagline', $designTagline), 'maxlength' => 80, 'help' => 'Empty = the tagline of the website (' . ($design['tagline'] !== '' ? $design['tagline'] : 'none set') . ').']) ?>
                    <div class="asoc-design__switches">
                        <label class="check"><input type="checkbox" name="show_logo" value="1"<?= $design['show_logo'] ? ' checked' : '' ?>><span>Show the logo</span></label>
                        <label class="check"><input type="checkbox" name="show_url" value="1"<?= $design['show_url'] ? ' checked' : '' ?>><span>Show the website address</span></label>
                    </div>
                    <div class="form-actions form-actions--plain">
                        <button class="btn btn--primary" type="submit">Save picture design</button>
                        <button class="btn btn--outline" type="submit" name="reset" value="1">Back to the brand colours</button>
                    </div>
                </form>
                <div class="asoc-design__preview">
                    <?php if ($previewId > 0 && $canRender): ?>
                        <p class="field__help">Preview with your newest issue (shows the saved design; save first to see a change):</p>
                        <img src="<?= e(url('admin/social/preview/' . $previewId . '/square')) ?>" alt="Preview of the share picture of the newest issue" width="540" height="540" loading="lazy">
                    <?php elseif ($previewId < 1): ?>
                        <p class="field__help">Write a newsletter issue to see a preview here.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
