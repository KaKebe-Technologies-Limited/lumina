<?php
/**
 * The invitee "smart box" (schedule form AND "add people" on the meeting page). OWNER: ADMIN agent. JS: assets/js/admin-meetings.js ([data-invitees]).
 * Works without JavaScript as a plain text area (comma, space, semicolon or new line separated, "Name <address>" accepted); the server parses it.
 * With JavaScript every person becomes a removable chip and the pickers (members, staff, earlier meetings) appear.
 * Posted: emails (the text area), items (JSON list of the chips), bulk[] (members_all | members_type:ID).
 * Vars: $idp (id prefix, unique per page), $people (initial chips), $groups (initial "all members" groups: spec, label, count), $existing (people already on the meeting: name,
 *       email, source, state: shown as locked chips), $canBulk, $lead (heading text), $hint (text under the box), $compact (bool).
 */
use App\Services\Meetings\MeetingService;

$idp = $idp ?? 'inv';
$people = $people ?? [];
$groups = $groups ?? [];
$existing = $existing ?? [];
$canBulk = !empty($canBulk);
$errText = error('emails');
$errInv = error('invitees');
$json = static fn(mixed $v): string => (string) json_encode($v, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<div class="ma-inv" data-invitees data-api="<?= e(url('admin/api/meetings')) ?>" data-can-bulk="<?= $canBulk ? '1' : '0' ?>"
     data-threshold="<?= (int) MeetingService::BULK_THRESHOLD ?>" data-max="<?= (int) MeetingService::MAX_ATTENDEES ?>" data-bulk-max="<?= (int) MeetingService::MAX_BULK_MEMBERS ?>"
     data-people="<?= e($json($people)) ?>" data-groups="<?= e($json($groups)) ?>" data-existing="<?= e($json($existing)) ?>">

    <div class="ma-inv__tabs" role="tablist" aria-label="Ways to add people" data-inv-tablist hidden>
        <button class="ma-seg__btn is-active" type="button" role="tab" id="<?= e($idp) ?>-tab-paste" aria-selected="true" aria-controls="<?= e($idp) ?>-panel-paste" data-inv-tab="paste"><?= icon('mail', 'icon--sm') ?> Type or paste</button>
        <button class="ma-seg__btn" type="button" role="tab" id="<?= e($idp) ?>-tab-members" aria-selected="false" aria-controls="<?= e($idp) ?>-panel-members" tabindex="-1" data-inv-tab="members"><?= icon('users', 'icon--sm') ?> Members</button>
        <button class="ma-seg__btn" type="button" role="tab" id="<?= e($idp) ?>-tab-staff" aria-selected="false" aria-controls="<?= e($idp) ?>-panel-staff" tabindex="-1" data-inv-tab="staff"><?= icon('user', 'icon--sm') ?> Our team</button>
        <button class="ma-seg__btn" type="button" role="tab" id="<?= e($idp) ?>-tab-previous" aria-selected="false" aria-controls="<?= e($idp) ?>-panel-previous" tabindex="-1" data-inv-tab="previous"><?= icon('history', 'icon--sm') ?> Earlier meetings</button>
    </div>

    <div class="ma-inv__panel" id="<?= e($idp) ?>-panel-paste" role="tabpanel" aria-labelledby="<?= e($idp) ?>-tab-paste" data-inv-panel="paste">
        <div class="field<?= $errText ? ' field--error' : '' ?>">
            <label class="field__label" for="<?= e($idp) ?>-text"><?= e($lead ?? 'E-mail addresses') ?></label>
            <textarea class="textarea ma-inv__text" id="<?= e($idp) ?>-text" name="emails" rows="3" data-inv-text autocomplete="off" spellcheck="false"
                      placeholder="amina@example.org, Peter Okello &lt;peter@example.org&gt;"
                      aria-describedby="<?= e($idp) ?>-text-help<?= $errText ? ' ' . e($idp) . '-text-error' : '' ?>"><?= e((string) old('emails', '')) ?></textarea>
            <div class="field__help" id="<?= e($idp) ?>-text-help">Type or paste as many as you like. Separate them with commas, spaces, semicolons or new lines. <span class="ma-nojs">Write them one after another; the list is checked when you save.</span> <span class="ma-js-only" hidden>Press <strong>Add to the list</strong> (or Ctrl+Enter) and each address becomes a chip.</span></div>
            <?php if ($errText): ?><div class="field__error" id="<?= e($idp) ?>-text-error" role="alert"><?= e($errText) ?></div><?php endif; ?>
        </div>
        <div class="ma-inv__addrow ma-js-only" hidden>
            <button class="btn btn--outline btn--sm" type="button" data-inv-add><?= icon('plus', 'icon--sm') ?> Add to the list</button>
        </div>
        <ul class="ma-inv__bad" data-inv-bad aria-label="Addresses that could not be added" hidden></ul>
    </div>

    <div class="ma-inv__panel" id="<?= e($idp) ?>-panel-members" role="tabpanel" aria-labelledby="<?= e($idp) ?>-tab-members" data-inv-panel="members" hidden>
        <div class="field">
            <label class="field__label" for="<?= e($idp) ?>-msearch">Search approved members</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" type="search" id="<?= e($idp) ?>-msearch" data-inv-msearch placeholder="Name, e-mail or membership number" autocomplete="off" maxlength="80"></div>
            <div class="field__help">Type at least two letters. Only approved members with an e-mail address are listed.</div>
        </div>
        <ul class="ma-results" data-inv-mresults aria-live="polite"></ul>
        <div class="ma-inv__bulk">
            <p class="ma-inv__bulkhead">Add many members at once</p>
            <div class="ma-inv__bulkrow">
                <button class="btn btn--outline btn--sm" type="button" data-inv-bulk-all>Add all approved members</button>
                <span class="ma-inv__or" aria-hidden="true">or</span>
                <label class="visually-hidden" for="<?= e($idp) ?>-mtype">Membership type</label>
                <select class="select" id="<?= e($idp) ?>-mtype" data-inv-mtype><option value="">Members of one type...</option></select>
                <button class="btn btn--outline btn--sm" type="button" data-inv-bulk-type disabled>Add this type</button>
            </div>
            <p class="field__help" data-inv-bulknote><?= $canBulk ? 'You will see how many people this adds before it happens.' : 'You will see how many people this adds before it happens. More than ' . (int) MeetingService::BULK_THRESHOLD . ' people at once is for administrators.' ?></p>
        </div>
    </div>

    <div class="ma-inv__panel" id="<?= e($idp) ?>-panel-staff" role="tabpanel" aria-labelledby="<?= e($idp) ?>-tab-staff" data-inv-panel="staff" hidden>
        <p class="field__help">Colleagues who have an account on this website.</p>
        <ul class="ma-results" data-inv-staff aria-live="polite"></ul>
    </div>

    <div class="ma-inv__panel" id="<?= e($idp) ?>-panel-previous" role="tabpanel" aria-labelledby="<?= e($idp) ?>-tab-previous" data-inv-panel="previous" hidden>
        <div class="field">
            <label class="field__label" for="<?= e($idp) ?>-psearch">Search people from earlier meetings</label>
            <div class="search-input"><?= icon('search', 'icon--sm') ?><input class="input" type="search" id="<?= e($idp) ?>-psearch" data-inv-psearch placeholder="Name or e-mail" autocomplete="off" maxlength="80"></div>
            <div class="field__help">Type at least two letters.</div>
        </div>
        <ul class="ma-results" data-inv-presults aria-live="polite"></ul>
    </div>

    <div class="ma-inv__list">
        <div class="ma-inv__listhead">
            <h3 class="ma-inv__title" id="<?= e($idp) ?>-listtitle">On the list</h3>
            <p class="ma-inv__count" aria-live="polite"><strong data-inv-count><?= count($people) + count($existing) ?></strong> <span data-inv-count-label>people</span></p>
        </div>
        <?php /* the ONE status line of the box: it lives here, not inside the "Type or paste" panel, so it stays visible (and is announced) whichever tab is open */ ?>
        <p class="ma-inv__msg" data-inv-msg role="status" aria-live="polite"></p>
        <?php if ($errInv): ?><div class="field__error" role="alert"><?= e($errInv) ?></div><?php endif; ?>
        <ul class="ma-chips" data-inv-chips aria-labelledby="<?= e($idp) ?>-listtitle"></ul>
        <p class="ma-inv__empty" data-inv-empty><?= e($hint ?? 'Nobody has been added yet. You can also save the meeting first and add people later.') ?></p>
    </div>

    <div data-inv-fields></div>
    <template data-inv-tpl-x><?= icon('x', 'icon--sm') ?></template>

    <p class="ma-inv__privacy" role="note"><?= icon('lock', 'icon--sm') ?> <span>Members' e-mail addresses are used only to send this invitation. Everyone gets their own message, so nobody sees who else is invited.</span></p>
</div>
