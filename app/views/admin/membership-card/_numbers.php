<?php
/**
 * Designer tab "Member numbers". OWNER: CARD agent. Vars: see index.php ($f, $values, $types, $prefix, $numberDefault, $tab).
 * The format is validated twice: instantly in the browser (assets/js/admin-membership-card.js) and by the server (CardSettings::numberFormatError), which is the
 * authority. The examples use the running number 1; real numbers continue from the highest number already issued in the same series.
 */
$t = $f['text'];
$format = (string) ($values['membership_card_number_format'] ?? $numberDefault);
$presets = [
    ['{PREFIX}-{TYPE}-{YY}-{SEQ}', 'Reference', 'Prefix, type, year and a counter per type and year'],
    ['{PREFIX}-{YYYY}-{SEQ}', 'Year only', 'The older style: one counter per year'],
    ['{PREFIX}-{TYPE}-{SEQ:5}', 'Type, no year', 'One counter per type, five digits'],
    ['{PREFIX}-{SEQ:6}', 'Simple', 'A single counter of six digits'],
];
?>
<section class="card mc-panel" id="tab-numbers" role="tabpanel" aria-labelledby="mc-tab-numbers" data-mc-panel="numbers">
    <?= partial('admin/membership-card/_panel_head', ['key' => 'numbers', 'tab' => $tab]) ?>
    <div class="card__body">
        <section class="form-section" aria-labelledby="mc-sec-format">
            <h3 class="form-section__title" id="mc-sec-format">Number format</h3>
            <div data-mc-numbers>
                <?= $t('membership_card_number_format', ['attrs' => ['data-mc-format' => true, 'spellcheck' => 'false', 'autocapitalize' => 'characters'], 'maxlength' => 60,
                    'help' => 'Build the number from the pieces below, with capital letters, digits and "-" between them. The running number {SEQ} always comes last.']) ?>
                <?= $f['chips']('#f-membership_card_number_format', [
                    '{PREFIX}' => 'Organisation prefix (' . $prefix . ')', '{TYPE}' => 'Code of the membership type', '{YY}' => 'Year, two digits (26)', '{YYYY}' => 'Year, four digits (2026)',
                    '{SEQ}' => 'Running number, 4 digits', '{SEQ:5}' => 'Running number, 5 digits', '{SEQ:6}' => 'Running number, 6 digits',
                ], 'Click to put a piece into the format') ?>
                <p class="mc-formatstatus" data-mc-format-status role="status" aria-live="polite"></p>

                <div class="mc-presets" role="group" aria-label="Number format examples">
                    <span class="mc-presets__title">Or start from one of these:</span>
                    <?php foreach ($presets as [$fmt, $name, $desc]): ?>
                        <button type="button" class="mc-preset" data-mc-format-preset="<?= e($fmt) ?>" title="<?= e($desc) ?>"><strong><?= e($name) ?></strong> <code><?= e($fmt) ?></code></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mc-numtable-wrap">
                <table class="table mc-numtable" aria-label="How a member number looks for each membership type">
                    <thead><tr><th scope="col">Membership type</th><th scope="col">Code</th><th scope="col">Example number (running number 1)</th></tr></thead>
                    <tbody>
                        <?php foreach ($types as $ty): ?>
                            <tr<?= $ty['active'] ? '' : ' class="is-inactive"' ?>>
                                <th scope="row"><?= e((string) $ty['type']['name']) ?><?= $ty['active'] ? '' : ' <span class="badge badge--neutral">switched off</span>' ?></th>
                                <td><code><?= e($ty['code']) ?></code></td>
                                <td><code data-mc-example="<?= (int) $ty['id'] ?>"><?= e($ty['next']) ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($types === []): ?><tr><td colspan="3">There are no membership types yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="field__help">The examples use the running number 1; real numbers continue from the highest number already issued in the same series (the same type and year, as far as the format contains them).
                <?php if (can('members.types')): ?>The code of each type is set under <a href="<?= e(url('admin/membership-types')) ?>">Membership types &amp; fees</a>.<?php endif; ?>
                <?php if (can('settings.manage')): ?>The prefix <code><?= e($prefix) ?></code> is set in <a href="<?= e(url('admin/settings')) ?>">Site settings</a> (Membership tab).<?php endif; ?></p>
        </section>

        <section class="form-section" aria-labelledby="mc-sec-numrules">
            <h3 class="form-section__title" id="mc-sec-numrules">Good to know</h3>
            <ul class="mc-notes">
                <li><strong>Numbers that were already issued never change.</strong> A new format only applies to numbers handed out from now on, so printed cards and old e-mails stay correct.</li>
                <li>Every number must stay unique. The running number counts up inside its series, and the system never hands out the same number twice, even when two people are approved at the same moment.</li>
                <li>Only capital letters, digits and "-" are allowed, so a number is safe in web links, file names and spreadsheets. The longest possible number may have 40 characters.</li>
                <li>Do not remove <code>{SEQ}</code> or move it: without a counter two members could get the same number.</li>
            </ul>
        </section>
    </div>
</section>
