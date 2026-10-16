<?php
/**
 * Dashboard. OWNER: B3. Vars: $user, $tiles, $recent{messages,applications,donations}, $chart (nullable), $activity, $allActivity,
 * $actions, $checklist, $launch, $canInbox.
 */
$first = trim((string) strtok((string) ($user['name'] ?? ''), ' '));
$hour = (int) date('G');
$greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<div class="admin-dashboard">
<?= partial('partials/admin/page-header', [
    'title'    => $greet . ($first !== '' ? ', ' . $first : ''),
    'subtitle' => 'Here is what needs your attention. Everything on the public website can be edited from this admin area.',
]) ?>

<?php if ($launch !== []): ?>
    <section class="dash-launch" aria-labelledby="launch-title">
        <h2 class="dash-launch__title" id="launch-title"><?= icon('alert-triangle', 'icon--sm') ?> Before you launch</h2>
        <ul class="dash-launch__list">
            <?php foreach ($launch as $item): ?>
                <li>
                    <div><strong><?= e($item['title']) ?></strong><span><?= e($item['text']) ?></span></div>
                    <a class="btn btn--outline btn--sm" href="<?= e(url($item['url'])) ?>"><?= e($item['cta']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if ($tiles !== []): ?>
    <div class="stat-grid dash-tiles">
        <?php foreach ($tiles as $t): ?>
            <a class="stat-tile<?= !empty($t['alert']) ? ' stat-tile--alert' : '' ?>" href="<?= e(url($t['url'])) ?>">
                <span class="stat-tile__icon"><?= icon($t['icon']) ?></span>
                <span class="stat-tile__text">
                    <span class="stat-tile__value<?= !empty($t['small']) ? ' stat-tile__value--small' : '' ?>"><?= e($t['value']) ?></span>
                    <span class="stat-tile__label"><?= e($t['label']) ?></span>
                    <?php if (!empty($t['sub'])): ?><span class="stat-tile__sub"><?= e($t['sub']) ?></span><?php endif; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card-grid card-grid--main-aside">
    <div class="dash-main">
        <?php if ($chart !== null): ?>
            <?= partial('admin/dashboard/_chart', ['chart' => $chart]) ?>
        <?php endif; ?>

        <?php if ($canInbox): ?>
            <div class="dash-recent">
                <section class="card" aria-labelledby="recent-messages">
                    <div class="card__header"><h2 class="card__title" id="recent-messages">Latest messages</h2><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/messages')) ?>">View all</a></div>
                    <?php if ($recent['messages'] === []): ?>
                        <p class="dash-empty">No messages yet. Messages sent from the Contact page will appear here.</p>
                    <?php else: ?>
                        <ul class="dash-list">
                            <?php foreach ($recent['messages'] as $m): ?>
                                <li>
                                    <a href="<?= e(url('admin/messages/' . (int) $m['id'])) ?>">
                                        <span class="dash-list__main"><strong><?= e($m['name']) ?></strong><span><?= e($m['subject'] !== null && $m['subject'] !== '' ? $m['subject'] : 'No subject') ?></span></span>
                                        <span class="dash-list__meta"><?= partial('partials/admin/status-badge', ['status' => $m['status']]) ?><time datetime="<?= e($m['created_at']) ?>"><?= e(format_datetime($m['created_at'], 'j M, H:i')) ?></time></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <section class="card" aria-labelledby="recent-apps">
                    <div class="card__header"><h2 class="card__title" id="recent-apps">Latest applications</h2><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/applications')) ?>">View all</a></div>
                    <?php if ($recent['applications'] === []): ?>
                        <p class="dash-empty">No applications yet. They arrive when someone applies for an open role on the Careers page.</p>
                    <?php else: ?>
                        <ul class="dash-list">
                            <?php foreach ($recent['applications'] as $a): ?>
                                <li>
                                    <a href="<?= e(url('admin/applications/' . (int) $a['id'])) ?>">
                                        <span class="dash-list__main"><strong><?= e($a['name']) ?></strong><span><?= e($a['job_title'] ?? 'Role no longer listed') ?></span></span>
                                        <span class="dash-list__meta"><?= partial('partials/admin/status-badge', ['status' => $a['status']]) ?><time datetime="<?= e($a['created_at']) ?>"><?= e(format_datetime($a['created_at'], 'j M, H:i')) ?></time></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>

                <section class="card dash-recent__wide" aria-labelledby="recent-gifts">
                    <div class="card__header"><h2 class="card__title" id="recent-gifts">Latest donations and pledges</h2><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/donations')) ?>">View all</a></div>
                    <?php if ($recent['donations'] === []): ?>
                        <p class="dash-empty">No donations or pledges recorded yet.</p>
                    <?php else: ?>
                        <ul class="dash-list">
                            <?php foreach ($recent['donations'] as $d): ?>
                                <li>
                                    <a href="<?= e(url('admin/donations')) ?>">
                                        <span class="dash-list__main"><strong><?= e((int) $d['is_anonymous'] === 1 ? 'Anonymous donor' : ($d['donor_name'] ?: 'Name not given')) ?></strong>
                                            <span><?= $d['amount'] !== null ? e($d['currency'] . ' ' . number_format((float) $d['amount'])) : 'Amount not given' ?></span></span>
                                        <span class="dash-list__meta"><?= partial('partials/admin/status-badge', ['status' => $d['status']]) ?><time datetime="<?= e($d['created_at']) ?>"><?= e(format_datetime($d['created_at'], 'j M, H:i')) ?></time></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            </div>
        <?php endif; ?>
    </div>

    <aside class="dash-aside" aria-label="Shortcuts and activity">
        <?php if ($actions !== []): ?>
            <section class="card" aria-labelledby="quick-actions">
                <div class="card__header"><h2 class="card__title" id="quick-actions">Quick actions</h2></div>
                <ul class="dash-actions">
                    <?php foreach ($actions as $a): ?>
                        <li><a href="<?= e(url($a['url'])) ?>"><?= icon($a['icon'], 'icon--sm') ?><span><?= e($a['label']) ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if ($checklist !== []): ?>
            <section class="card" aria-labelledby="content-checklist">
                <div class="card__header"><h2 class="card__title" id="content-checklist">Organisation pages</h2></div>
                <p class="dash-note">These pages are live and in the menu. Edit their wording in Pages; add the people, files and items they show here.</p>
                <ul class="dash-check">
                    <?php foreach ($checklist as $c): ?>
                        <li class="<?= $c['count'] > 0 ? 'is-done' : '' ?>">
                            <span class="dash-check__mark" aria-hidden="true"><?= $c['count'] > 0 ? icon('check', 'icon--sm') : '' ?></span>
                            <span class="dash-check__text"><strong><?= e($c['title']) ?></strong>
                                <?php if ($c['count'] > 0): ?><span><?= (int) $c['count'] ?> published</span> <a href="<?= e(url($c['url'])) ?>">Manage</a>
                                <?php else: ?><a href="<?= e(url($c['add'])) ?>"><?= e($c['add_label']) ?></a><?php endif; ?>
                                <span><a href="<?= e(url($c['edit'])) ?>">Edit page text</a></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <section class="card" aria-labelledby="recent-activity">
            <div class="card__header">
                <h2 class="card__title" id="recent-activity"><?= $allActivity ? 'Recent activity' : 'Your recent activity' ?></h2>
                <?php if ($allActivity): ?><a class="btn btn--ghost btn--sm" href="<?= e(url('admin/audit-log')) ?>">Full log</a><?php endif; ?>
            </div>
            <?php if ($activity === []): ?>
                <p class="dash-empty">Nothing recorded yet. Edits made in this admin area are listed here.</p>
            <?php else: ?>
                <ul class="dash-activity">
                    <?php foreach ($activity as $a): ?>
                        <li>
                            <span class="dash-activity__text"><?= e($a['summary'] !== '' ? $a['summary'] : label_from(str_replace('.', '_', (string) $a['action']))) ?></span>
                            <span class="dash-activity__meta"><?= $allActivity ? e($a['user_name']) . ' &middot; ' : '' ?><time datetime="<?= e($a['created_at']) ?>"><?= e(format_datetime($a['created_at'], 'j M, H:i')) ?></time></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </aside>
</div>
</div>
