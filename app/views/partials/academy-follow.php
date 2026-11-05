<?php
/**
 * "Follow and subscribe to our pages". Each page opens through /academy/go/{page} (so the click is recorded), the learner types their username on the platforms we can look up,
 * and (when it is required) ticks the box that unlocks once every page was opened (assets/js/academy.js, [data-follow]). Vars: $required (bool, default false).
 * Fields: follow (tick), handle_<platform> (usernames).
 */
$required = !empty($required);
$pages = \App\Services\Academy\AcademySocial::pages();
if ($pages === []) {
    return;
}
$fields = $required ? \App\Services\Academy\AcademySocial::fields() : [];
$opened = \App\Services\Academy\AcademySocial::opened();
?>
<div class="ac-follow field<?= error('follow') ? ' field--error' : '' ?>" role="group" aria-labelledby="ac-follow-title" data-follow<?= $required ? ' data-follow-required' : '' ?>>
    <p class="ac-follow__title" id="ac-follow-title"><?= icon('heart', 'icon--sm') ?> Follow and subscribe to our pages<?= $required ? ' <abbr title="required" aria-label="required">*</abbr>' : '' ?></p>
    <p class="ac-follow__text">Stay close to Lumina Africa Foundation and the LAF Academy: news, new courses, classes and opportunities are shared on our pages.
        <?php if ($required): ?><strong>Open every page below, follow or subscribe, then come back.</strong> The Academy team checks that you follow us.<?php else: ?>Open each one and follow or subscribe.<?php endif; ?></p>
    <ul class="ac-follow__links">
        <?php foreach ($pages as $k => $l): $done = in_array($k, $opened, true); ?>
            <li><a class="btn btn--outline btn--sm<?= $done ? ' is-opened' : '' ?>" href="<?= e(url('academy/go/' . $k)) ?>" target="_blank" rel="noopener" data-follow-link="<?= e($k) ?>"><?= icon((string) $l['icon'], 'icon--sm') ?> <?= e($l['label']) ?><span class="ac-follow__done" aria-hidden="true"<?= $done ? '' : ' hidden' ?>> &#10003;</span></a></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($fields !== []): ?>
        <div class="ac-follow__handles">
            <p class="ac-follow__text"><strong>Your usernames</strong> (so we can find you in our followers):</p>
            <?php foreach ($fields as $k): [$label, $req] = \App\Services\Academy\AcademySocial::HANDLES[$k]; ?>
                <div class="field<?= error('handle_' . $k) ? ' field--error' : '' ?>">
                    <label class="field__label" for="handle-<?= e($k) ?>"><?= e($label) ?> username<?= $req ? ' <abbr title="required" aria-label="required">*</abbr>' : ' (optional)' ?></label>
                    <input class="input" id="handle-<?= e($k) ?>" name="handle_<?= e($k) ?>" type="text" maxlength="80" autocomplete="off" value="<?= e((string) old('handle_' . $k)) ?>" placeholder="@yourname"<?= $req ? ' required aria-required="true"' : '' ?>>
                    <?php if (error('handle_' . $k)): ?><div class="field__error" role="alert"><?= e((string) error('handle_' . $k)) ?></div><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($required): ?>
        <label class="ac-follow__check"><input type="checkbox" name="follow" value="1" required aria-required="true" data-follow-tick<?= old('follow') ? ' checked' : '' ?>> I follow and subscribe to the pages above</label>
        <p class="ac-follow__hint" data-follow-hint hidden>Open every page above first: the box unlocks when you have.</p>
    <?php endif; ?>
    <?php if (error('follow')): ?><div class="field__error" role="alert"><?= e((string) error('follow')) ?></div><?php endif; ?>
</div>
