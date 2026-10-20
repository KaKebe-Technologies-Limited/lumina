<?php
/**
 * Admin > Social media > Auto-share channels (ability social.manage). OWNER: ADMIN agent.
 * A card per channel: on / off switch, plain-language set-up steps, the fields (secrets are write-only: "Set (hidden)" + Replace / Remove), token expiry with a reminder,
 * TEST CONNECTION (read-only) and SEND A TEST POST (confirm dialog), last status. Works without JavaScript (the test buttons post a form and flash the answer).
 * Vars: $channels (id => ['meta' => Channels meta, 'view' => SocialConfig::publicView, 'queued' => int]), $global, $canEncrypt, $devBase, $attention.
 * A secret input is always empty: the stored value never reaches the page.
 */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-social.js')) ?>" defer></script>
<?php $this->end();

$tz = new \DateTimeZone(date_default_timezone_get());
$stateText = static fn(string $s): string => match ($s) { 'ok' => 'Works', 'needs_token' => 'Needs a new token', 'error' => 'Problem', default => 'Not tested yet' };
$readyOn = 0;
$workingOn = 0;
foreach ($channels as $c) {
    if ($c['view']['ready']['ready']) { $readyOn++; if ($c['view']['status']['state'] === 'ok') { $workingOn++; } }
}
?>
<div class="asoc">
    <?= partial('partials/admin/page-header', [
        'title'    => 'Auto-share channels',
        'subtitle' => 'When you publish a newsletter issue, it can be posted to your accounts by itself. Every channel needs YOUR OWN developer keys: none are built in.',
        'actions'  => can('social.share') ? [['label' => 'Share log', 'url' => url('admin/social/log'), 'class' => 'btn--outline', 'icon' => 'history'], ['label' => 'Share kit', 'url' => url('admin/social/kit'), 'class' => 'btn--outline', 'icon' => 'image']] : [],
    ]) ?>

    <div class="alert alert--warning" role="note"><?= icon('alert-triangle') ?><div class="alert__body"><strong class="alert__title">Try each channel with a test post before you rely on it.</strong>
        These connections were written from the platforms' public documentation and were only checked against a practice copy on a developer's computer, never against the real services.
        "Test connection" proves the keys are accepted; nothing is promised until you have seen a real test post appear on the account.
        <strong>TikTok has no direct channel</strong>: its posting service cannot publish a text and link newsletter. Use the Webhook channel with an automation tool (Zapier, Make, IFTTT, Buffer)<?= can('social.share') ? ' or the <a href="' . e(url('admin/social/kit')) . '">Share kit</a> to post by hand in a minute' : ' or the Share kit (to post by hand in a minute)' ?>.</div></div>
    <?php if ($devBase): ?><div class="alert alert--info" role="status"><?= icon('info') ?><div class="alert__body"><strong class="alert__title">Developer mode.</strong> The channels are pointed at a local practice server (SOCIAL_DEV_BASE_URL). This works only on a local computer.</div></div><?php endif; ?>
    <?php if (!$canEncrypt): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><strong class="alert__title">Secrets cannot be stored on this server.</strong> The OpenSSL extension is missing. Put the keys in the .env file instead (the names are shown under each box).</div></div><?php endif; ?>
    <?php if (!empty($attention['needs_token'])): ?><div class="alert alert--error" role="alert"><?= icon('alert-circle') ?><div class="alert__body"><strong class="alert__title">A channel needs a new access token:</strong>
        <?= e(implode(', ', array_map([\App\Services\Social\Channels::class, 'label'], $attention['needs_token']))) ?>. Paste a fresh token in its card below: posts that failed because of the old one are queued again.</div></div><?php endif; ?>

    <section class="card" aria-labelledby="ch-all">
        <form method="post" action="<?= e(url('admin/social/channels/global')) ?>" novalidate data-unsaved-guard>
            <?= csrf_field() ?>
            <div class="card__header"><h2 class="card__title" id="ch-all">All channels</h2><span class="asoc-count"><?= (int) $readyOn ?> of <?= count($channels) ?> set up, <?= (int) $workingOn ?> tested OK</span></div>
            <div class="card__body">
                <div class="asoc-master">
                    <label class="switch"><input type="checkbox" name="enabled" value="1"<?= $global['enabled'] ? ' checked' : '' ?>><span class="switch__track"></span><span class="switch__text">Auto-share is ON</span></label>
                    <span class="asoc-master__hint">The master switch: switch it off to stop every channel at once. Each channel below also has its own switch.</span>
                </div>
                <nav class="asoc-chips" aria-label="Jump to a channel">
                    <?php foreach ($channels as $id => $c): $v = $c['view']; $m = $c['meta'];
                        $tone = $v['ready']['ready'] ? 'ok' : ($v['status']['state'] === 'needs_token' ? 'bad' : ($v['enabled'] ? 'warn' : 'off')); ?>
                        <a class="asoc-chip asoc-chip--<?= $tone ?>" href="#<?= e($id) ?>"><?= icon($m['icon'], 'icon--sm') ?><span><?= e($m['label']) ?></span><span class="asoc-chip__state"><?= $v['ready']['ready'] ? ($v['status']['state'] === 'ok' ? 'Tested OK' : 'Set up, not tested') : ($v['enabled'] ? 'Not ready' : 'Off') ?></span></a>
                    <?php endforeach; ?>
                </nav>
                <div class="form-grid">
                    <div class="field field--full">
                        <label class="check"><input type="checkbox" name="utm" value="1"<?= $global['utm'] ? ' checked' : '' ?>><span>Add tracking tags to the shared link <small>(<code>utm_source=&lt;platform&gt;&amp;utm_medium=social&amp;utm_campaign=newsletter</code>), so your visitor statistics show which platform sent them</small></span></label>
                    </div>
                    <?= partial('partials/form-field', ['name' => 'hashtags', 'label' => 'Default hashtags', 'value' => (string) old('hashtags', $global['hashtags']), 'maxlength' => 600, 'placeholder' => '#YourOrganisation #Uganda', 'help' => 'Used where a template has {hashtags}. Separate with spaces.']) ?>
                    <?= partial('partials/form-field', ['name' => 'graph_version', 'label' => 'Meta (Facebook / Instagram) API version', 'value' => (string) old('graph_version', $global['graph_version']), 'maxlength' => 8, 'help' => 'Like v23.0. Change it only if Meta says the version is retired.']) ?>
                    <?= partial('partials/form-field', ['name' => 'li_version', 'label' => 'LinkedIn API version', 'value' => (string) old('li_version', $global['li_version']), 'maxlength' => 6, 'help' => 'Year and month, like 202510. Change it only if LinkedIn says the version is retired.']) ?>
                </div>
            </div>
            <div class="card__footer"><button class="btn btn--primary" type="submit">Save these options</button></div>
        </form>
    </section>

    <?php foreach ($channels as $id => $c):
        $m = $c['meta']; $v = $c['view']; $st = $v['status']; $ready = $v['ready']['ready'];
        $formId = 'chform-' . $id;
        $badgeTone = $ready ? ($st['state'] === 'ok' ? 'success' : 'info') : ($st['state'] === 'needs_token' ? 'danger' : ($v['enabled'] ? 'warning' : 'neutral'));
        $badgeText = $ready ? ($st['state'] === 'ok' ? 'On, tested OK' : 'On, not tested yet') : ($st['state'] === 'needs_token' ? 'Needs a new token' : ($v['enabled'] ? ($v['ready']['reasons'][0] ?? 'Not ready') : 'Off'));
        $anySet = false;
        foreach ($v['fields'] as $f) { if ($f['type'] === 'secret' ? !empty($f['secret']['set']) : (!in_array($f['type'], ['select', 'multi'], true) && ($f['value'] ?? '') !== '')) { $anySet = true; } }
        $values = [];
        foreach ($v['fields'] as $f) { if ($f['type'] !== 'secret') { $values[$f['key']] = (string) old($f['key'], (string) $f['value']); } }
    ?>
        <section class="card asoc-channel" id="<?= e($id) ?>" aria-labelledby="ch-<?= e($id) ?>" data-asoc-card="<?= e($id) ?>">
            <div class="card__header asoc-channel__head">
                <h2 class="card__title" id="ch-<?= e($id) ?>"><span class="asoc-chicon asoc-chicon--<?= e($id) ?>"><?= icon($m['icon']) ?></span> <?= e($m['label']) ?></h2>
                <span class="badge badge--<?= $badgeTone ?> badge--dot asoc-badge"><?= e($badgeText) ?></span>
                <label class="switch asoc-channel__switch"><input type="checkbox" name="enabled" value="1" form="<?= e($formId) ?>"<?= $v['enabled'] ? ' checked' : '' ?>><span class="switch__track"></span><span class="switch__text">Switch on</span></label>
            </div>
            <div class="card__body">
                <p class="asoc-channel__short"><?= e($m['short']) ?></p>
                <?php if (!empty($m['needs'])): ?><p class="asoc-channel__needs"><?= icon('key', 'icon--sm') ?> <span><?= e($m['needs']) ?></span></p><?php endif; ?>
                <?php foreach ((array) $m['notes'] as $n): ?><p class="asoc-channel__note"><?= icon('info', 'icon--sm') ?> <span><?= e($n) ?></span></p><?php endforeach; ?>

                <details class="asoc-steps"<?= $anySet ? '' : ' open' ?>>
                    <summary>How to set it up</summary>
                    <ol><?php foreach ((array) $m['steps'] as $s): ?><li><?= e($s) ?></li><?php endforeach; ?></ol>
                    <?php if ($m['setup_url'] !== ''): ?><p><a class="btn btn--outline btn--sm" href="<?= e($m['setup_url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon('external-link', 'icon--sm') ?> Open the platform's page for creating keys</a></p><?php endif; ?>
                    <?php if (!empty($m['permissions'])): ?><p class="asoc-steps__perm"><strong>Permissions needed:</strong> <?= e($m['permissions']) ?></p><?php endif; ?>
                </details>

                <p class="asoc-health asoc-health--<?= e($st['state']) ?>" data-asoc-health>
                    <?php if ($st['state'] === 'untested'): ?>Not tested yet. Save your keys, then press "Test connection".
                    <?php else: ?>Last check: <strong><?= e($stateText($st['state'])) ?></strong><?= $st['at'] !== '' ? ' (' . e($st['at']) . ')' : '' ?><?= $st['message'] !== '' ? ': ' . e($st['message']) : '' ?><?php endif; ?>
                </p>

                <form method="post" action="<?= e(url('admin/social/channels/' . $id)) ?>" id="<?= e($formId) ?>" novalidate autocomplete="off" data-unsaved-guard data-asoc-channel-form>
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <?php foreach ($v['fields'] as $f):
                            $fid = 'f-' . $id . '-' . $f['key'];
                            $when = !empty($f['when']) ? ' data-when="' . e((string) json_encode($f['when'])) . '"' : '';
                            $err = error($f['key']);
                        ?>
                            <?php if ($f['type'] === 'secret'): $sec = $f['secret']; $isEnv = ($sec['source'] ?? '') === 'env'; $isSet = !empty($sec['set']); ?>
                                <div class="field asoc-secret<?= $err ? ' field--error' : '' ?>" data-asoc-secret data-set="<?= $isSet && !$isEnv ? '1' : '0' ?>" data-field="<?= e($f['key']) ?>"<?= $when ?>>
                                    <label class="field__label" for="<?= e($fid) ?>"><?= e($f['label']) ?><?= $f['required'] ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></label>
                                    <div class="asoc-secret__state">
                                        <span class="badge badge--<?= $isSet ? 'success' : 'neutral' ?>"><?= e($sec['text']) ?></span>
                                        <?php if ($isSet && !$isEnv): ?>
                                            <button type="button" class="btn btn--outline btn--sm" data-secret-replace hidden>Replace</button>
                                            <label class="check asoc-secret__remove"><input type="checkbox" name="clear_<?= e($f['key']) ?>" value="1"><span>Remove</span></label>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$isEnv): ?>
                                        <input class="input" type="password" id="<?= e($fid) ?>" name="<?= e($f['key']) ?>" value="" autocomplete="new-password" spellcheck="false" maxlength="<?= (int) $f['maxlength'] ?>" data-secret-input
                                               placeholder="<?= $isSet ? 'Type a new value to replace it' : 'Paste it here' ?>"<?= $err ? ' aria-invalid="true"' : '' ?>>
                                    <?php endif; ?>
                                    <div class="field__help"><?= e($f['help']) ?><?= $f['env'] !== '' ? ($f['help'] !== '' ? ' ' : '') . 'It can also be set in the .env file as ' . e($f['env']) . '.' : '' ?></div>
                                    <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
                                </div>
                            <?php elseif ($f['type'] === 'select'): ?>
                                <div class="field<?= $err ? ' field--error' : '' ?>"<?= $when ?> data-field="<?= e($f['key']) ?>">
                                    <label class="field__label" for="<?= e($fid) ?>"><?= e($f['label']) ?></label>
                                    <select class="select" id="<?= e($fid) ?>" name="<?= e($f['key']) ?>" data-asoc-select>
                                        <?php foreach ($f['options'] as $ov => $ol): ?><option value="<?= e($ov) ?>"<?= (string) $ov === $values[$f['key']] ? ' selected' : '' ?>><?= e($ol) ?></option><?php endforeach; ?>
                                    </select>
                                    <?php if ($f['help'] !== ''): ?><div class="field__help"><?= e($f['help']) ?></div><?php endif; ?>
                                    <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
                                </div>
                            <?php elseif ($f['type'] === 'multi'): $sel = array_filter(explode(',', (string) $f['value'])); ?>
                                <fieldset class="field field--full asoc-multi"<?= $when ?> data-field="<?= e($f['key']) ?>"><legend class="field__label"><?= e($f['label']) ?></legend>
                                    <div class="asoc-multi__list"><?php foreach ($f['options'] as $ov => $ol): ?><label class="check"><input type="checkbox" name="<?= e($f['key']) ?>[]" value="<?= e($ov) ?>"<?= in_array($ov, $sel, true) ? ' checked' : '' ?>><span><?= e($ol) ?></span></label><?php endforeach; ?></div>
                                    <?php if ($f['help'] !== ''): ?><div class="field__help"><?= e($f['help']) ?></div><?php endif; ?></fieldset>
                            <?php else:
                                $isDate = $f['type'] === 'date'; $val = $values[$f['key']];
                                $left = null;
                                if ($isDate && $val !== '' && ($d = \DateTimeImmutable::createFromFormat('!Y-m-d', $val, $tz)) !== false) {
                                    $left = (int) (new \DateTimeImmutable('today', $tz))->diff($d)->format('%r%a');
                                }
                            ?>
                                <div class="field<?= $err ? ' field--error' : '' ?>"<?= $when ?> data-field="<?= e($f['key']) ?>">
                                    <label class="field__label" for="<?= e($fid) ?>"><?= e($f['label']) ?><?= $f['required'] ? ' <span class="field__req" aria-hidden="true">*</span>' : '' ?></label>
                                    <input class="input" type="<?= $isDate ? 'date' : 'text' ?>" id="<?= e($fid) ?>" name="<?= e($f['key']) ?>" value="<?= e($val) ?>" maxlength="<?= (int) $f['maxlength'] ?>" autocomplete="off" spellcheck="false"
                                           <?= $f['placeholder'] !== '' ? 'placeholder="' . e($f['placeholder']) . '"' : '' ?><?= $err ? ' aria-invalid="true"' : '' ?>>
                                    <?php if ($left !== null): ?>
                                        <div class="asoc-expiry asoc-expiry--<?= $left < 0 ? 'bad' : ($left <= 14 ? 'warn' : 'ok') ?>"><?= icon($left <= 14 ? 'alert-triangle' : 'clock', 'icon--sm') ?>
                                            <?= $left < 0 ? 'This token EXPIRED ' . abs($left) . ' day' . (abs($left) === 1 ? '' : 's') . ' ago: paste a new one.' : ($left === 0 ? 'This token expires today.' : 'Expires in ' . $left . ' day' . ($left === 1 ? '' : 's') . '.' . ($left <= 14 ? ' Get a new one soon.' : '')) ?></div>
                                    <?php endif; ?>
                                    <?php if ($f['help'] !== ''): ?><div class="field__help"><?= e($f['help']) ?><?= $isDate ? ' You get a reminder on the dashboard and by e-mail 14 days before.' : '' ?></div><?php endif; ?>
                                    <?php if ($err): ?><div class="field__error" role="alert"><?= e($err) ?></div><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="asoc-channel__opts">
                        <label class="check"><input type="checkbox" name="default_on" value="1"<?= $v['default_on'] ? ' checked' : '' ?>><span>Tick this channel by default when an issue is published <small>(you can still untick it for a single issue)</small></span></label>
                    </div>
                    <div class="form-actions"><button class="btn btn--primary" type="submit">Save <?= e($m['label']) ?></button>
                        <?php if ($c['queued'] > 0): ?><a class="asoc-channel__queued" href="<?= e(url('admin/social/log?channel=' . $id)) ?>"><?= (int) $c['queued'] ?> post<?= $c['queued'] === 1 ? '' : 's' ?> waiting or needing attention</a><?php endif; ?></div>
                </form>

                <div class="asoc-tests" data-asoc-tests>
                    <div class="asoc-tests__row">
                        <form method="post" action="<?= e(url('admin/social/channels/' . $id . '/test')) ?>" data-asoc-test data-no-guard><?= csrf_field() ?>
                            <button class="btn btn--outline" type="submit"><?= icon('refresh', 'icon--sm') ?> Test connection</button></form>
                        <form method="post" action="<?= e(url('admin/social/channels/' . $id . '/send-test')) ?>" data-asoc-sendtest data-no-guard
                              data-confirm="This posts a SHORT TEST MESSAGE to the real <?= e($m['label']) ?> account (<?= e($m['test_post'] === 'test_chat' ? 'to the test chat when one is set, otherwise to the real channel' : ($m['test_post'] === 'webhook_flag' ? 'marked as a test for the receiving tool' : 'you can delete it on the platform afterwards')) ?>). Continue?">
                            <?= csrf_field() ?><input type="hidden" name="confirm" value="1"><button class="btn btn--ghost" type="submit"><?= icon('send', 'icon--sm') ?> Send a test post</button></form>
                    </div>
                    <p class="asoc-tests__help">"Test connection" only reads (nothing is posted). "Send a test post" really posts a short message, after you confirm. Save your changes first: both use what is saved.</p>
                    <p class="asoc-result" data-asoc-result role="status" aria-live="polite" hidden></p>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</div>
