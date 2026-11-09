<?php
/** Admin > Official letters > send: choose the addresses and how. Vars: $l, $contacts, $groups, $defaultMessage, $adminEmail. Nothing is sent from this page. */
$id = (int) $l['id'];
$this->start('scripts'); ?><script src="<?= e(asset('js/admin-letters.js')) ?>" defer></script><?php $this->end(); ?>
<?= partial('partials/admin/page-header', ['title' => 'Send: ' . $l['subject'], 'subtitle' => 'Step 1 of 2: choose who receives the letter. You will see the full list before anything is sent.', 'actions' => [['label' => 'Back to the preview', 'url' => url('admin/letters/' . $id . '/preview'), 'icon' => 'arrow-left', 'class' => 'btn--ghost']]]) ?>
<form method="post" action="<?= e(url('admin/letters/' . $id . '/review')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="ad-letter-grid">
        <section class="card"><div class="card__body">
            <h2 class="card__title">Who receives it</h2>
            <div class="field"><label class="field__label" for="typed">Type or paste e-mail addresses</label>
                <textarea class="textarea" id="typed" name="typed" rows="8" placeholder="mary@example.org&#10;John Okello &lt;john@example.org&gt;&#10;Grace Auma, grace@example.org, Acme Ltd"></textarea>
                <span class="field__hint">One per line, or separated by semicolons. A name before the address ("Name &lt;email&gt;" or "Name, email") lets the letter greet that person by name.</span></div>
            <?php if ($groups): ?>
                <div class="field"><label class="field__label" for="group">Or a whole group from the contact book</label>
                    <select class="select" id="group" name="group"><option value="">None</option><?php foreach ($groups as $g): ?><option><?= e($g) ?></option><?php endforeach; ?></select></div>
            <?php endif; ?>
            <?php if ($contacts): ?>
                <fieldset class="ad-letter-contacts"><legend class="field__label">Or tick contacts</legend>
                    <?php foreach ($contacts as $c): ?><label class="check"><input type="checkbox" name="contacts[]" value="<?= (int) $c['id'] ?>"> <?= e(($c['name'] !== '' ? $c['name'] . ' - ' : '') . $c['email']) ?><?= $c['grp'] ? ' <small class="text-muted">(' . e($c['grp']) . ')</small>' : '' ?></label><?php endforeach; ?>
                </fieldset>
            <?php endif; ?>
            <p class="text-muted"><a href="<?= e(url('admin/letters/contacts')) ?>"><?= icon('users', 'icon--sm') ?> Manage the contact book</a></p>
        </div></section>

        <section class="card"><div class="card__body">
            <h2 class="card__title">How it is sent</h2>
            <label class="check"><input type="radio" name="mode" value="each" checked data-send-mode> <strong>One e-mail for each person</strong> (private: nobody sees the others)</label>
            <label class="check ad-letter-sub2" data-opt-each><input type="checkbox" name="personalise" value="1"> Address each letter to the person by name (the PDF greeting becomes "Dear Name,")</label>
            <label class="check"><input type="radio" name="mode" value="one" data-send-mode> <strong>One e-mail to the whole group</strong></label>
            <label class="check ad-letter-sub2" data-opt-one hidden><input type="checkbox" name="hide" value="1" checked> Hide the addresses from each other (recommended)</label>

            <div class="field"><label class="field__label" for="subject">E-mail subject</label><input class="input" id="subject" name="subject" maxlength="300" value="<?= e('Official letter: ' . $l['subject']) ?>"></div>
            <div class="field"><label class="field__label" for="message">Words in the e-mail (the PDF is attached)</label><textarea class="textarea" id="message" name="message" rows="5" maxlength="4000"><?= e($defaultMessage) ?></textarea></div>
            <div class="field"><label class="field__label" for="reply_to">Replies go to (optional)</label><input class="input" type="email" id="reply_to" name="reply_to" value="<?= e($adminEmail) ?>"></div>
            <label class="check"><input type="checkbox" name="again" value="1"> Send again to addresses that already received this letter</label>
            <p class="text-muted">Gmail allows about 500 e-mails a day (about 2,000 on Google Workspace). At most 100 are sent each time you press Send: press it again for the rest.</p>
        </div></section>
    </div>
    <div class="form-actions"><button class="btn btn--primary" type="submit"><?= icon('check', 'icon--sm') ?> Check the list</button><a class="btn btn--ghost" href="<?= e(url('admin/letters/' . $id . '/preview')) ?>">Cancel</a></div>
</form>
