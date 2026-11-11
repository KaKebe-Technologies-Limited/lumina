<?php
/**
 * Dashboard. OWNER: B3. Vars: $user, $tiles, $recent{messages,applications,donations}, $chart (nullable), $activity, $allActivity,
 * $actions, $checklist, $launch, $canInbox, $pay (money collected: range, sum, ev, feed; null unless the user manages payments).
 */
$this->start('scripts'); ?><script src="<?= e(asset('js/dash-rings.js')) ?>" defer></script><?php $this->end();
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

<?php if (!empty($pay)):
    $sum = $pay['sum'];
    $ev = $pay['ev'];
    $cats = ['membership' => ['Membership', '#2c7be5'], 'shop' => ['Shop', '#e4572e'], 'donations' => ['Donations', '#d9a441'], 'projects' => ['Projects', '#1f9d8a'], 'academy' => ['Academy fees', '#7b5ea7']];
    $totalCat = max(0, array_sum(array_map(static fn(array $c): int => (int) $c['amt'], $sum['by_cat'])));
    $mk = static fn(int $minor): string => number_format((int) round($minor / 100));
    $rangeLabels = \App\Services\Payments\PaymentsDashboard::RANGES;
    $cum = 0.0;
    $finished = max(1, $ev['success'] + $ev['failed']);
    $evIcon = ['started' => 'clock', 'requested' => 'send', 'unknown' => 'clock', 'success' => 'check-circle', 'failed' => 'alert-circle', 'reversed' => 'alert-circle', 'review' => 'alert-triangle', 'thanks' => 'mail'];
    $evGroup = \App\Services\Payments\PaymentEvents::EVENTS;
?>
    <section class="dash-pay" aria-labelledby="dash-pay-title" data-dash-pay>
        <div class="dash-pay__head">
            <h2 class="dash-pay__title" id="dash-pay-title"><?= icon('trending-up', 'icon--sm') ?> Money collected</h2>
            <nav class="pd-range pd-range--small" aria-label="Period">
                <?php foreach ($rangeLabels as $k => $label): ?><a class="pd-range__item<?= $pay['range'] === (string) $k ? ' is-active' : '' ?>" href="<?= e(url('admin?range=' . $k)) ?>"<?= $pay['range'] === (string) $k ? ' aria-current="true"' : '' ?>><?= e($label) ?></a><?php endforeach; ?>
            </nav>
        </div>
        <div class="dash-pay__grid">
            <div class="dash-ring" data-ring>
                <svg class="dash-ring__svg" viewBox="0 0 36 36" role="img" aria-label="Money collected by category">
                    <circle class="dash-ring__track" cx="18" cy="18" r="15.9155"></circle>
                    <g transform="rotate(-90 18 18)">
                        <?php foreach ($cats as $k => [$label, $color]):
                            $amt = (int) ($sum['by_cat'][$k]['amt'] ?? 0);
                            $p = $totalCat > 0 ? $amt / $totalCat * 100 : 0;
                            if ($p <= 0) { continue; } ?>
                            <circle class="dash-ring__seg" data-ring-seg="<?= e($k) ?>" cx="18" cy="18" r="15.9155" stroke="<?= e($color) ?>" stroke-dasharray="<?= e(number_format(max(0, $p - 0.6), 3, '.', '')) ?> <?= e(number_format(100 - max(0, $p - 0.6), 3, '.', '')) ?>" stroke-dashoffset="<?= e(number_format(-$cum, 3, '.', '')) ?>" tabindex="0" role="button" aria-label="<?= e($label . ': UGX ' . $mk($amt)) ?>"></circle>
                            <?php $cum += $p; endforeach; ?>
                    </g>
                </svg>
                <div class="dash-ring__center" aria-live="polite">
                    <span class="dash-ring__small" data-ring-label>Collected</span>
                    <strong class="dash-ring__big" data-ring-value data-default="UGX <?= e($mk((int) $sum['in'])) ?>">UGX <?= e($mk((int) $sum['in'])) ?></strong>
                    <span class="dash-ring__small" data-ring-sub data-default="<?= e(strtolower($rangeLabels[$pay['range']])) ?>"><?= e(strtolower($rangeLabels[$pay['range']])) ?></span>
                </div>
            </div>
            <ul class="dash-legend">
                <?php foreach ($cats as $k => [$label, $color]):
                    $amt = (int) ($sum['by_cat'][$k]['amt'] ?? 0); $n = (int) ($sum['by_cat'][$k]['n'] ?? 0); $pc = $totalCat > 0 ? (int) round($amt / $totalCat * 100) : 0; ?>
                    <li><a class="dash-legend__item" href="<?= e(url('admin/payments?range=' . $pay['range'] . '&category=' . $k)) ?>" data-ring-item="<?= e($k) ?>" data-label="<?= e($label) ?>" data-value="UGX <?= e($mk($amt)) ?>" data-sub="<?= $pc ?>% &middot; <?= $n ?> payment<?= $n === 1 ? '' : 's' ?>">
                        <span class="dash-legend__dot" style="background:<?= e($color) ?>"></span>
                        <span class="dash-legend__text"><strong><?= e($label) ?></strong><small><?= $n ?> payment<?= $n === 1 ? '' : 's' ?></small></span>
                        <span class="dash-legend__amt">UGX <?= e($mk($amt)) ?></span>
                    </a></li>
                <?php endforeach; ?>
            </ul>
            <div class="dash-health">
                <div class="dash-ring dash-ring--small">
                    <svg class="dash-ring__svg" viewBox="0 0 36 36" role="img" aria-label="Payment success rate">
                        <circle class="dash-ring__track" cx="18" cy="18" r="15.9155"></circle>
                        <g transform="rotate(-90 18 18)">
                            <circle class="dash-ring__seg" cx="18" cy="18" r="15.9155" stroke="#1f9d4a" stroke-dasharray="<?= e(number_format($ev['success'] / $finished * 100, 3, '.', '')) ?> 100" stroke-dashoffset="0"></circle>
                            <circle class="dash-ring__seg" cx="18" cy="18" r="15.9155" stroke="#d64545" stroke-dasharray="<?= e(number_format($ev['failed'] / $finished * 100, 3, '.', '')) ?> 100" stroke-dashoffset="<?= e(number_format(-$ev['success'] / $finished * 100, 3, '.', '')) ?>"></circle>
                        </g>
                    </svg>
                    <div class="dash-ring__center"><strong class="dash-ring__big"><?= (int) $ev['rate'] ?>%</strong><span class="dash-ring__small">success</span></div>
                </div>
                <ul class="dash-health__list">
                    <li><a href="<?= e(url('admin/payments/audit?range=' . $pay['range'] . '&group=attempt')) ?>"><span class="dash-health__dot dash-health__dot--wait"></span> <strong><?= (int) $ev['started'] ?></strong> attempts</a></li>
                    <li><a href="<?= e(url('admin/payments/audit?range=' . $pay['range'] . '&group=success')) ?>"><span class="dash-health__dot dash-health__dot--ok"></span> <strong><?= (int) $ev['success'] ?></strong> successful</a></li>
                    <li><a href="<?= e(url('admin/payments/audit?range=' . $pay['range'] . '&group=failure')) ?>"><span class="dash-health__dot dash-health__dot--bad"></span> <strong><?= (int) $ev['failed'] ?></strong> unsuccessful</a></li>
                </ul>
            </div>
        </div>
        <div class="dash-pay__actions">
            <a class="btn btn--outline btn--sm" href="<?= e(url('admin/payments')) ?>"><?= icon('bar-chart', 'icon--sm') ?> All payments</a>
            <a class="btn btn--outline btn--sm" href="<?= e(url('admin/payments/audit')) ?>"><?= icon('list', 'icon--sm') ?> Audit trail</a>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/payments#send')) ?>"><?= icon('send', 'icon--sm') ?> Send money</a>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/daraza/api')) ?>"><?= icon('phone', 'icon--sm') ?> Payment options</a>
        </div>

        <div class="dash-feed">
            <div class="dash-feed__head">
                <h3 class="dash-feed__title">Latest payment activity</h3>
                <div class="dash-chips" role="group" aria-label="Show">
                    <button class="dash-chip is-on" type="button" data-feed-filter="all">All</button>
                    <button class="dash-chip" type="button" data-feed-filter="attempt">Attempts</button>
                    <button class="dash-chip" type="button" data-feed-filter="success">Successful</button>
                    <button class="dash-chip" type="button" data-feed-filter="failure">Unsuccessful</button>
                </div>
            </div>
            <?php if (!$pay['feed']): ?><p class="dash-empty">No payment steps yet. Every attempt, success and failure will appear here.</p><?php else: ?>
            <ul class="dash-feed__list">
                <?php foreach ($pay['feed'] as $r): $g = $evGroup[$r['event']] ?? 'notice'; ?>
                    <li class="dash-feed__item dash-feed__item--<?= e($g) ?>" data-feed-group="<?= e($g) ?>">
                        <span class="dash-feed__icon"><?= icon($evIcon[$r['event']] ?? 'info', 'icon--sm') ?></span>
                        <span class="dash-feed__text"><strong><?= e(\App\Services\Payments\PaymentEvents::EVENT_LABELS[$r['event']] ?? $r['event']) ?></strong> &middot; <?= e(\App\Services\Payments\PaymentEvents::CATEGORIES[$r['category']] ?? $r['category']) ?><?= $r['who'] !== '' ? ' &middot; ' . e($r['who']) : '' ?>
                            <small><?= e($r['detail']) ?> <code><?= e($r['reference']) ?></code></small></span>
                        <span class="dash-feed__meta"><?php if ($r['amount_minor'] !== null): ?><b><?= e($r['currency']) ?> <?= e($mk((int) $r['amount_minor'])) ?></b><?php endif; ?><time datetime="<?= e($r['created_at']) ?>"><?= e(format_datetime($r['created_at'], 'j M, H:i')) ?></time></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </section>
    <section class="dash-pay dash-dz" aria-labelledby="dash-dz-title" data-dash-dz>
        <?php $dz = $pay['dz']; $dzCats = \App\Services\Payments\PaymentEvents::CATEGORIES; ?>
        <div class="dash-pay__head">
            <h2 class="dash-pay__title" id="dash-dz-title"><?= icon('phone', 'icon--sm') ?> Daraza Pay collections</h2>
            <div class="dash-chips" role="group" aria-label="Show">
                <button class="dash-chip is-on" type="button" data-dz-filter="all">All (<?= (int) $dz['total'] ?>)</button>
                <button class="dash-chip" type="button" data-dz-filter="paid">Paid (<?= (int) $dz['paid'] ?>)</button>
                <button class="dash-chip" type="button" data-dz-filter="waiting">Waiting (<?= (int) $dz['waiting'] ?>)</button>
                <button class="dash-chip" type="button" data-dz-filter="failed">Failed (<?= (int) $dz['failed'] ?>)</button>
            </div>
        </div>
        <div class="dash-dz__kpis">
            <div><span>Collected with Daraza Pay</span><strong>UGX <?= e(number_format((int) $dz['paid_ugx'])) ?></strong></div>
            <div><span><span class="paynet__tag paynet__tag--mtn dash-dz__tag">MTN</span></span><strong>UGX <?= e(number_format((int) $dz['mtn'])) ?></strong></div>
            <div><span><span class="paynet__tag paynet__tag--airtel dash-dz__tag">Airtel</span></span><strong>UGX <?= e(number_format((int) $dz['airtel'])) ?></strong></div>
        </div>
        <?php if (!$dz['rows']): ?><p class="dash-empty">No Daraza Pay payment in this period yet. Each collection will be listed here with who paid, when, the country and what it was for.</p><?php else: ?>
        <div class="table-wrap"><table class="table table--compact dash-dz__table">
            <caption class="visually-hidden">Payments collected with Daraza Pay</caption>
            <thead><tr><th scope="col">Who paid</th><th scope="col">Category</th><th scope="col">Country</th><th scope="col">Time</th><th scope="col" class="num">Amount (UGX)</th><th scope="col">Status</th></tr></thead>
            <tbody>
            <?php foreach ($dz['rows'] as $r):
                $grp = $r['status'] === 'paid' ? 'paid' : ($r['status'] === 'failed' ? 'failed' : 'waiting'); ?>
                <tr data-dz-group="<?= e($grp) ?>">
                    <td data-label="Who paid"><strong><?= e($r['who']) ?></strong><span class="cell-sub"><?= e(\App\Services\Payments\PaymentEvents::maskPhone($r['phone'])) ?> <?= e([1 => 'MTN', 2 => 'Airtel'][(int) $r['method']] ?? '') ?></span></td>
                    <td data-label="Category"><?= e($dzCats[$r['category']] ?? $r['category']) ?><span class="cell-sub"><?= e(\App\Services\DarazaPay\DarazaPayments::PURPOSES[$r['purpose']] ?? '') ?></span></td>
                    <td data-label="Country"><?= e($r['country'] !== '' ? $r['country'] : '—') ?></td>
                    <td class="nowrap" data-label="Time"><time datetime="<?= e($r['at']) ?>"><?= e(format_datetime($r['at'], 'j M Y, H:i')) ?></time></td>
                    <td class="num nowrap" data-label="Amount"><?= e(number_format((int) $r['amount_ugx'])) ?></td>
                    <td data-label="Status"><?php $stale = $grp === 'waiting' && strtotime((string) $r['at']) < time() - 600; ?><span class="badge badge--<?= $grp === 'paid' ? 'success' : ($grp === 'failed' ? 'danger' : 'warning') ?>"><?= e($stale ? 'Not confirmed' : ['paid' => 'Paid', 'failed' => 'Failed', 'waiting' => 'Waiting'][$grp]) ?></span><?php if ($stale): ?><a class="cell-sub" href="<?= e(url('admin/payments?range=all&status=pending&channel=mobile')) ?>">Resolve</a><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
        <div class="dash-pay__actions">
            <a class="btn btn--outline btn--sm" href="<?= e(url('admin/payments?range=' . $pay['range'] . '&channel=mobile')) ?>"><?= icon('list', 'icon--sm') ?> All Daraza Pay payments</a>
            <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/payments/audit?range=' . $pay['range'] . '&channel=mobile')) ?>">Audit trail</a>
        </div>
    </section>
<?php endif; ?>

<?php if ($tiles !== []): ?>
    <section class="dash-orbs-wrap" aria-labelledby="dash-orbs-title">
        <h2 class="dash-orbs__title" id="dash-orbs-title">Needs your attention</h2>
        <div class="dash-orbs">
            <?php foreach ($tiles as $t): ?>
                <a class="orb<?= !empty($t['alert']) ? ' orb--alert' : '' ?>" href="<?= e(url($t['url'])) ?>">
                    <span class="orb__ring"><span class="orb__icon"><?= icon($t['icon'], 'icon--sm') ?></span><strong class="orb__value<?= !empty($t['small']) || mb_strlen((string) $t['value']) > 5 ? ' orb__value--small' : '' ?>"><?= e($t['value']) ?></strong></span>
                    <span class="orb__label"><?= e($t['label']) ?></span>
                    <?php if (!empty($t['sub'])): ?><span class="orb__sub"><?= e($t['sub']) ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($academy) && (int) $academy['overview']['learners'] > 0): ?>
    <section class="card dash-academy" aria-labelledby="dash-academy-title">
        <div class="card__header"><h2 class="card__title" id="dash-academy-title"><?= icon('book', 'icon--sm') ?> LAF Academy at a glance</h2><span class="dash-academy__links"><a class="btn btn--outline btn--sm" href="<?= e(url('admin/academy/statistics#share')) ?>"><?= icon('send', 'icon--sm') ?> Share on social media</a> <a class="btn btn--ghost btn--sm" href="<?= e(url('admin/academy/statistics')) ?>">All statistics <?= icon('arrow-right', 'icon--arrow') ?></a></span></div>
        <div class="card__body ad-page ad-page--admin">
            <?= partial('partials/academy-charts', ['part' => 'kpis', 'overview' => $academy['overview']]) ?>
            <div class="ad-grid">
                <div class="ad-panel"><h3 class="ad-panel__title"><?= icon('layers', 'icon--sm') ?> Learners by topic</h3><?= partial('partials/academy-charts', ['part' => 'topics', 'topics' => $academy['topics'], 'limit' => 6]) ?></div>
                <div class="ad-panel"><h3 class="ad-panel__title"><?= icon('globe', 'icon--sm') ?> Learners by country</h3><?= partial('partials/academy-charts', ['part' => 'countries', 'countries' => $academy['countries']]) ?></div>
            </div>
        </div>
    </section>
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
