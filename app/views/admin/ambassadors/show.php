<?php
/**
 * Admin > one ambassador application, redesigned: a profile header (portrait, number, stage, score), quick links (e-mail, call, WhatsApp, CV, profile), a stage tracker with
 * one-click stage buttons (the applicant is e-mailed), and the application in tidy sections with the judging panel beside it.
 * Vars: $r, $regions, $canDelete, $reviewer.
 */
use App\Models\AmbassadorApplication as Amb;
use App\Services\Inbox\Support;
use App\Services\Recruitment\Recruitment;

$id = (int) $r['id'];
$status = (string) $r['status'];
$mailto = Support::mailto($r['email']);
$tel = phone_href($r['phone'] ?? '');
$wa = '';
if (!empty($r['phone']) && str_starts_with(trim((string) $r['phone']), '+')) {
    $wa = 'https://wa.me/' . preg_replace('/\D+/', '', (string) $r['phone']);
}
$profile = !empty($r['profile_url']) && preg_match('~^https?://~i', (string) $r['profile_url']) === 1 ? (string) $r['profile_url'] : '';
$hasCv = trim((string) ($r['cv_path'] ?? '')) !== '';
$hasPortrait = trim((string) ($r['photo_path'] ?? '')) !== '';
$roles = array_map(static fn(string $k): string => Amb::ROLES[$k] ?? $k, Amb::jsonList($r['roles']));
$pledgeTypes = Amb::pledgeList($r);
$items = array_map(static fn(string $k): string => Amb::INKIND[$k] ?? $k, Amb::jsonList($r['inkind_items'] ?? ''));
$pct = $r['score_pct'] ?? null;
$level = Recruitment::threshold();
$labels = Recruitment::statuses('ambassador');
$steps = ['new' => 'Received', 'reviewing' => 'Under review', 'interview' => 'Interview', 'approved' => 'Approved'];
$pos = array_search($status, array_keys($steps), true);
$location = ($r['city'] ? $r['city'] . ', ' : '') . $r['country'] . ' (' . ($regions[$r['region']] ?? $r['region']) . ')';
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/admin-inbox.js')) ?>" defer></script>
<?php $this->end(); ?>
<div class="admin-inbox ra">
    <header class="ra-hero card">
        <div class="ra-hero__who">
            <?php if ($hasPortrait): ?>
                <a href="<?= e(url('admin/recruitment/ambassador/' . $id . '/portrait')) ?>" target="_blank" rel="noopener"><img class="ra-hero__pic" src="<?= e(url('admin/recruitment/ambassador/' . $id . '/portrait')) ?>" alt="Portrait of <?= e($r['name']) ?>" width="112" height="112"></a>
            <?php else: ?><span class="ra-hero__pic ra-hero__pic--none" aria-hidden="true"><?= icon('user') ?></span><?php endif; ?>
            <div class="ra-hero__text">
                <p class="ra-hero__eyebrow"><span class="rc-ref"><?= e((string) ($r['ref_no'] ?? '')) ?></span> <?= partial('partials/admin/status-badge', ['status' => $status, 'label' => $labels[$status] ?? null]) ?></p>
                <h1 class="ra-hero__name"><?= e($r['name']) ?></h1>
                <p class="ra-hero__meta"><?= icon('globe', 'icon--sm') ?> <?= e($location) ?><?php if (!empty($r['occupation'])): ?> &middot; <?= e($r['occupation']) ?><?= !empty($r['organisation']) ? ', ' . e($r['organisation']) : '' ?><?php endif; ?></p>
                <p class="ra-hero__meta text-muted">Applied <?= e(format_datetime($r['created_at'], 'j F Y, H:i')) ?><?= $r['reviewed_at'] ? ' &middot; last changed ' . e(format_datetime($r['reviewed_at'], 'j M Y')) . ($reviewer !== '' ? ' by ' . e($reviewer) : '') : '' ?></p>
            </div>
            <div class="ra-score <?= $pct !== null && (float) $pct >= $level ? 'is-pass' : '' ?>" title="Weighted score of the judging criteria">
                <?php if ($pct !== null): ?><strong><?= e(rtrim(rtrim(number_format((float) $pct, 1), '0'), '.')) ?>%</strong><span>score</span>
                <?php else: ?><strong>-</strong><span>not scored</span><?php endif; ?>
            </div>
        </div>
        <nav class="ra-quick" aria-label="Quick links">
            <?php if ($mailto !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e($mailto) ?>"><?= icon('mail', 'icon--sm') ?> E-mail</a><?php endif; ?>
            <?php if ($tel !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e($tel) ?>"><?= icon('phone', 'icon--sm') ?> Call</a><?php endif; ?>
            <?php if ($wa !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e($wa) ?>" target="_blank" rel="noopener noreferrer"><?= icon('message-circle', 'icon--sm') ?> WhatsApp</a><?php endif; ?>
            <?php if ($hasCv): ?><a class="btn btn--outline btn--sm" href="<?= e(url('admin/recruitment/ambassador/' . $id . '/cv')) ?>"><?= icon('download', 'icon--sm') ?> CV</a><?php endif; ?>
            <?php if ($profile !== ''): ?><a class="btn btn--outline btn--sm" href="<?= e($profile) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= icon('external-link', 'icon--sm') ?> Profile</a><?php endif; ?>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('track-application')) ?>" target="_blank" rel="noopener"><?= icon('search', 'icon--sm') ?> What the applicant sees</a>
            <span class="ra-quick__sep" aria-hidden="true"></span>
            <a class="btn btn--ghost btn--sm" href="#motivation">Why</a>
            <a class="btn btn--ghost btn--sm" href="#pledge">Pledge</a>
            <a class="btn btn--ghost btn--sm" href="#judge">Judge</a>
            <a class="btn btn--ghost btn--sm" href="#notes">Notes</a>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/ambassadors')) ?>"><?= icon('arrow-left', 'icon--sm') ?> All</a>
        </nav>
    </header>

    <section class="card ra-stage" aria-label="Stage of the application">
        <ol class="ra-steps">
            <?php $i = 0; foreach ($steps as $k => $label): $state = $pos === false ? 'todo' : ($i < $pos ? 'done' : ($i === $pos ? 'now' : 'todo')); ?>
                <li class="ra-step ra-step--<?= $state ?>"><span class="ra-step__dot"><?= $state === 'done' ? icon('check', 'icon--sm') : $i + 1 ?></span><span class="ra-step__label"><?= e($label) ?></span></li>
            <?php $i++; endforeach; ?>
        </ol>
        <?php if (in_array($status, ['on_hold', 'declined'], true)): ?><p class="ra-stage__flag ra-stage__flag--<?= e($status) ?>"><?= e($labels[$status]) ?></p><?php endif; ?>
        <form method="post" action="<?= e(url('admin/recruitment/ambassador/' . $id . '/status')) ?>" class="ra-actions">
            <?= csrf_field() ?>
            <div class="ra-actions__btns">
                <?php if ($status === 'new'): ?><button class="btn btn--primary btn--sm" name="status" value="reviewing" type="submit"><?= icon('eye', 'icon--sm') ?> Start the review</button><?php endif; ?>
                <?php if (!in_array($status, ['interview', 'approved'], true)): ?><button class="btn btn--primary btn--sm" name="status" value="interview" type="submit"><?= icon('calendar', 'icon--sm') ?> Invite to interview</button><?php endif; ?>
                <?php if ($status !== 'approved'): ?><button class="btn btn--primary btn--sm" name="status" value="approved" type="submit"><?= icon('check-circle', 'icon--sm') ?> Approve and onboard</button><?php endif; ?>
                <?php if ($status !== 'on_hold'): ?><button class="btn btn--outline btn--sm" name="status" value="on_hold" type="submit"><?= icon('clock', 'icon--sm') ?> Put on hold</button><?php endif; ?>
                <?php if ($status !== 'declined'): ?><button class="btn btn--ghost btn--sm btn-danger-hover" name="status" value="declined" type="submit" data-confirm="Decline this application? The applicant is e-mailed."><?= icon('x', 'icon--sm') ?> Decline</button><?php endif; ?>
            </div>
            <details class="ra-actions__note">
                <summary>Add a message to the e-mail the applicant receives (optional)</summary>
                <textarea class="textarea" name="applicant_note" rows="3" maxlength="1000" placeholder="For example: we will call you on Monday to agree the interview time."></textarea>
            </details>
            <p class="text-muted ra-actions__hint">Each button moves the application and e-mails the applicant at once. At <?= (int) $level ?>% or more on the judging criteria they are invited to an interview automatically.</p>
        </form>
    </section>

    <div class="card-grid card-grid--main-aside">
        <div class="inbox-main">
            <section class="card" id="motivation">
                <div class="card__header"><h2 class="card__title"><?= icon('heart', 'icon--sm') ?> Why they want to be an ambassador</h2></div>
                <div class="card__body">
                    <?php if (trim((string) $r['motivation']) !== ''): ?><div class="ra-clamp inbox-message"><?= nl2p((string) $r['motivation']) ?></div><?php else: ?><p class="text-muted">They did not write anything.</p><?php endif; ?>
                    <?php if ($roles): ?><h3 class="inbox-subheading">Could help by</h3><ul class="ra-chips"><?php foreach ($roles as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul><?php endif; ?>
                    <?php if (trim((string) $r['network']) !== ''): ?><h3 class="inbox-subheading">Who they could reach</h3><div class="ra-clamp inbox-message"><?= nl2p((string) $r['network']) ?></div><?php endif; ?>
                </div>
            </section>

            <section class="card" id="pledge">
                <div class="card__header"><h2 class="card__title"><?= icon('gift', 'icon--sm') ?> Yearly pledge</h2></div>
                <div class="card__body">
                    <div class="ra-pledges">
                        <?php if (in_array('money', $pledgeTypes, true) && $r['annual_amount'] !== null): ?>
                            <div class="ra-pledge"><span class="ra-pledge__k">Money</span><strong class="ra-pledge__v"><?= e(number_format((float) $r['annual_amount'], 0)) ?> <?= e((string) $r['currency']) ?></strong><span class="text-muted">per year</span></div>
                        <?php endif; ?>
                        <?php if (in_array('inkind', $pledgeTypes, true)): ?>
                            <div class="ra-pledge"><span class="ra-pledge__k">In kind</span><strong class="ra-pledge__v"><?= $items ? e(implode(', ', $items)) : 'See notes' ?></strong><?php if (trim((string) ($r['inkind_notes'] ?? '')) !== ''): ?><span class="text-muted"><?= e((string) $r['inkind_notes']) ?></span><?php endif; ?></div>
                        <?php endif; ?>
                        <?php if (in_array('other', $pledgeTypes, true) && trim((string) ($r['other_text'] ?? '')) !== ''): ?>
                            <div class="ra-pledge ra-pledge--wide"><span class="ra-pledge__k">Something else</span><details class="ra-more"><summary><?= e(mb_strimwidth((string) $r['other_text'], 0, 220, '...')) ?><span class="ra-more__open"> Read all</span></summary><div class="inbox-message"><?= nl2p((string) $r['other_text']) ?></div></details></div>
                        <?php endif; ?>
                        <?php if (!$pledgeTypes): ?><p class="text-muted">No pledge details.</p><?php endif; ?>
                    </div>
                    <?php if ($r['annual_amount'] !== null): ?><p class="text-muted">This is what they could raise or give in a year, as they entered it. It is an intention, not a payment.</p><?php endif; ?>
                </div>
            </section>

            <section class="card">
                <div class="card__header"><h2 class="card__title"><?= icon('user', 'icon--sm') ?> About the applicant</h2></div>
                <div class="card__body">
                    <dl class="definition-list">
                        <dt>E-mail</dt><dd><?php if ($mailto !== ''): ?><a href="<?= e($mailto) ?>"><?= e($r['email']) ?></a><?php else: ?><?= e($r['email']) ?><?php endif; ?></dd>
                        <?php if (!empty($r['phone'])): ?><dt>Phone</dt><dd><a href="<?= e(phone_href($r['phone'])) ?>"><?= e($r['phone']) ?></a></dd><?php endif; ?>
                        <dt>Location</dt><dd><?= e($location) ?></dd>
                        <?php if (!empty($r['availability'])): ?><dt>Time they can give</dt><dd><?= e(Amb::AVAILABILITY[$r['availability']] ?? $r['availability']) ?></dd><?php endif; ?>
                        <dt>May be shown</dt><dd><?= $r['public_ok'] ? 'Yes: name and country on the Ambassadors page once approved' : 'No' ?></dd>
                    </dl>
                </div>
            </section>
        </div>

        <aside class="inbox-aside" aria-label="Judging and notes">
            <?= partial('admin/recruitment/_panel', ['scope' => 'ambassador', 'row' => $r, 'parts' => ['judge']]) ?>

            <section class="card" id="notes">
                <div class="card__header"><h2 class="card__title">Private notes</h2></div>
                <form method="post" action="<?= e(url('admin/ambassadors/' . $id)) ?>" data-unsaved-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="<?= e($status) ?>">
                    <div class="card__body">
                        <?= partial('partials/form-field', ['name' => 'admin_notes', 'label' => 'Notes', 'type' => 'textarea', 'rows' => 5, 'value' => old('admin_notes', (string) ($r['admin_notes'] ?? '')), 'maxlength' => 5000, 'help' => 'Who spoke to them and what was agreed. Only the team sees this.']) ?>
                    </div>
                    <div class="card__footer"><button class="btn btn--primary btn--sm" type="submit"><?= icon('save', 'icon--sm') ?> Save the notes</button></div>
                </form>
            </section>

            <?= partial('admin/recruitment/_panel', ['scope' => 'ambassador', 'row' => $r, 'parts' => ['history']]) ?>
            <?= partial('admin/inbox/_meta', ['row' => $r, 'title' => 'Technical details']) ?>
            <?php if ($canDelete): ?>
                <section class="card inbox-danger"><div class="card__body">
                    <form method="post" action="<?= e(url('admin/ambassadors/' . $id . '/delete')) ?>" data-confirm="Delete this application permanently? This cannot be undone.">
                        <?= csrf_field() ?><button class="btn btn--danger btn--sm" type="submit"><?= icon('trash', 'icon--sm') ?> Delete application</button>
                    </form>
                </div></section>
            <?php endif; ?>
        </aside>
    </div>
</div>
