<?php
/** Admin > Data protection. Vars: $counts, $requests, $types, $retention, $checklist, $activities, $days. */
$icons = ['ok' => 'check-circle', 'warn' => 'alert-triangle', 'todo' => 'alert-circle'];
$tone = ['ok' => 'ok', 'warn' => 'warn', 'todo' => 'bad'];
$todo = count(array_filter($checklist, static fn(array $c): bool => $c['state'] !== 'ok'));
?>
<?= partial('partials/admin/page-header', ['title' => 'Data protection', 'subtitle' => 'What personal data the website collects, requests from people about their data, retention reminders, and what the owner must still confirm. This is a draft framework, not legal advice.',
    'actions' => [['label' => 'View privacy policy', 'url' => url('privacy'), 'icon' => 'external-link', 'class' => 'btn--outline'], ['label' => 'Edit policy text', 'url' => url('admin/pages/privacy/edit'), 'icon' => 'edit', 'class' => 'btn--ghost']]]) ?>

<div class="af-tiles">
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--<?= $counts['open'] > 0 ? 'gold' : 'green' ?>"><?= icon('inbox') ?></span><div><span class="af-tile__label">Open requests</span><strong class="af-tile__value"><?= (int) $counts['open'] ?></strong><span class="af-tile__sub">We aim to answer in <?= (int) $days ?> days</span></div></div>
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--<?= $counts['overdue'] > 0 ? 'red' : 'green' ?>"><?= icon('clock') ?></span><div><span class="af-tile__label">Past the target</span><strong class="af-tile__value"><?= (int) $counts['overdue'] ?></strong><span class="af-tile__sub"><?= (int) $counts['due_soon'] ?> due within 7 days</span></div></div>
    <div class="af-tile"><span class="af-tile__icon"><?= icon('check-circle') ?></span><div><span class="af-tile__label">Answered</span><strong class="af-tile__value"><?= (int) $counts['done'] ?></strong><span class="af-tile__sub">replied or archived</span></div></div>
    <div class="af-tile"><span class="af-tile__icon af-tile__icon--<?= $todo > 0 ? 'gold' : 'green' ?>"><?= icon('shield') ?></span><div><span class="af-tile__label">Owner checklist</span><strong class="af-tile__value"><?= (int) $todo ?></strong><span class="af-tile__sub">items still to confirm</span></div></div>
</div>

<section class="card af-card" id="requests"><div class="card__body">
    <h2 class="card__title">Data rights requests</h2>
    <p class="text-muted">People send these from the public page <a href="<?= e(url('privacy/request')) ?>">Your data rights</a>. Each one is also in <a href="<?= e(url('admin/messages')) ?>">Messages</a>, where you reply and change its status. Replying and verifying the person's identity are done by a person, not by the website.</p>
    <?php if (!$requests): ?>
        <p class="text-muted">No requests yet.</p>
    <?php else: ?>
        <div class="table-wrap"><table class="table">
            <thead><tr><th scope="col">Reference</th><th scope="col">Request</th><th scope="col">From</th><th scope="col">Status</th><th scope="col">Answer by</th></tr></thead>
            <tbody>
            <?php foreach ($requests as $r): $late = in_array($r['status'], ['new', 'read'], true) && $r['due_at'] !== null && $r['due_at'] < date('Y-m-d'); ?>
                <tr>
                    <td><a href="<?= e(url('admin/messages/' . (int) $r['id'])) ?>"><?= e((string) $r['reference']) ?></a></td>
                    <td><?= e($types[$r['request_type']] ?? (string) $r['request_type']) ?></td>
                    <td><?= e((string) $r['name']) ?><br><span class="text-muted"><?= e((string) $r['email']) ?></span></td>
                    <td><?= e(ucfirst((string) $r['status'])) ?></td>
                    <td><?= $r['due_at'] ? e(date('j M Y', strtotime((string) $r['due_at']))) : '-' ?><?= $late ? ' <span class="sec-badge sec-badge--bad">late</span>' : '' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</div></section>

<section class="card af-card" id="retention"><div class="card__body">
    <h2 class="card__title">Retention reminders</h2>
    <p class="text-muted">Records older than the aims stated in the privacy policy. Nothing is deleted automatically: review each list and delete or anonymise what is no longer needed.</p>
    <div class="table-wrap"><table class="table">
        <thead><tr><th scope="col">Records</th><th scope="col">Aim</th><th scope="col">How many</th></tr></thead>
        <tbody>
        <?php foreach ($retention as $r): ?>
            <tr><td><a href="<?= e(url($r['where'])) ?>"><?= e($r['label']) ?></a></td><td><?= e($r['target']) ?></td><td><strong><?= (int) $r['count'] ?></strong></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></section>

<section class="card af-card" id="inventory"><div class="card__body">
    <h2 class="card__title">What the website collects (summary)</h2>
    <p class="text-muted">The full inventory (fields, storage, who sees it, third parties, gaps) is in <code>docs/DATA-INVENTORY.md</code>. The public table is edited in Pages &gt; Privacy Policy &gt; "Table: what we collect". Each form below shows its "Why we ask for this" note.</p>
    <ul class="dp-list">
        <?php foreach ($activities as $key => $a): ?>
            <li><a href="<?= e(\App\Services\Privacy\Notices::url($key)) ?>" target="_blank" rel="noopener"><?= e($a[0]) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div></section>

<section class="card af-card" id="checklist"><div class="card__body">
    <h2 class="card__title">Owner checklist <span class="sec-badge sec-badge--<?= $todo > 0 ? 'warn' : 'ok' ?>"><?= $todo > 0 ? $todo . ' to confirm' : 'All done' ?></span></h2>
    <p class="text-muted"><strong>Draft framework, not legal advice.</strong> Please have the wording confirmed by a lawyer and check the foundation's registration with the Personal Data Protection Office (PDPO) of Uganda. This list is visible only here, never on the public site.</p>
    <ul class="sec-list">
        <?php foreach ($checklist as $c): ?>
            <li class="sec-item sec-item--<?= e($tone[$c['state']] ?? 'bad') ?>">
                <span class="sec-item__icon"><?= icon($icons[$c['state']] ?? 'alert-circle') ?></span>
                <div><strong><?= e($c['title']) ?></strong><p><?= e($c['detail']) ?></p></div>
            </li>
        <?php endforeach; ?>
    </ul>
</div></section>
