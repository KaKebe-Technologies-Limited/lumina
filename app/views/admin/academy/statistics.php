<?php
/** Admin > Academy > Statistics. Vars: $d (overview, topics, countries, months, funnel, matrix), $ready. Aggregates only; the charts are the shared partial partials/academy-charts. */
$o = (array) $d['overview'];
$exp = static fn(string $w): string => url('admin/academy/statistics/export', ['what' => $w]);
?>
<?= partial('partials/admin/page-header', ['title' => 'Academy statistics', 'subtitle' => 'Learners by topic and by country, growth and certificates. The same dashboard is public at /academy/impact (totals only).', 'actions' => [['label' => 'Open the public dashboard', 'url' => url('academy/impact'), 'icon' => 'external-link', 'class' => 'btn--outline']]]) ?>

<?php if (!$ready): ?>
    <div class="alert alert--warning" role="status"><strong>The Academy tables are missing.</strong> Open Admin > LAF Academy once so the Academy installs itself, then come back.</div>
<?php else: ?>
    <div class="ad-page ad-page--admin">
        <?php if (!empty($share)): ?>
        <details class="card ad-share" id="share" data-share-box>
            <summary class="card__body ad-share__sum"><span><?= icon('send', 'icon--sm') ?> <strong>Share these numbers on social media</strong></span><span class="btn btn--primary btn--sm">Share</span></summary>
            <div class="card__body">
                <p class="text-muted">The post below is written from the real figures above (totals only: no names, no e-mails) and links to the public dashboard. Edit the words if you like, then press a button: that network opens with your post ready to publish. Nothing is posted until you confirm it there.</p>
                <div class="field"><label class="field__label" for="share-caption">The post</label>
                    <textarea class="textarea" id="share-caption" rows="7" maxlength="1500" data-share-caption data-share-link="<?= e($share['link']) ?>"><?= e($share['caption']) ?></textarea></div>
                <p class="ad-share__count text-muted"><span data-share-count>0</span> characters. X allows 280 (a link counts as 23): shorten the post for X if the counter is red.</p>
                <div class="ad-share__row">
                    <?php foreach ([
                        ['facebook', 'Facebook', 'facebook', 'https://www.facebook.com/sharer/sharer.php?u={url}&quote={text}'],
                        ['x', 'X', 'x', 'https://twitter.com/intent/tweet?text={text}'],
                        ['linkedin', 'LinkedIn', 'linkedin', 'https://www.linkedin.com/sharing/share-offsite/?url={url}'],
                        ['whatsapp', 'WhatsApp', 'whatsapp', 'https://wa.me/?text={text}'],
                        ['telegram', 'Telegram', 'telegram', 'https://t.me/share/url?url={url}&text={text}'],
                    ] as [$k, $label, $icon, $tpl]): ?>
                        <button class="btn btn--outline btn--sm" type="button" data-share-to="<?= e($tpl) ?>"><?= icon($icon, 'icon--sm') ?> <?= e($label) ?></button>
                    <?php endforeach; ?>
                    <button class="btn btn--ghost btn--sm" type="button" data-share-copy><?= icon('copy', 'icon--sm') ?> <span data-share-copy-label>Copy the post</span></button>
                </div>
                <?php if (!empty($share['pages'])): ?>
                    <h3 class="ad-share__sub">Post on our own pages</h3>
                    <p class="text-muted">Instagram, TikTok and YouTube cannot be shared from a link: copy the post, then open our page and paste it (add a screenshot of the dashboard).</p>
                    <div class="ad-share__row">
                        <?php foreach ($share['pages'] as $pg): ?>
                            <a class="btn btn--ghost btn--sm" href="<?= e($pg['url']) ?>" target="_blank" rel="noopener noreferrer"><?= icon((string) $pg['icon'], 'icon--sm') ?> Open our <?= e($pg['label']) ?> page</a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Our social media page addresses are not set yet: add them in Admin > Social media to get a button for each page here.</p>
                <?php endif; ?>
            </div>
        </details>
        <?php $this->start('scripts'); ?><script src="<?= e(asset('js/admin-share-stats.js')) ?>" defer></script><?php $this->end(); ?>
        <?php endif; ?>

        <?= partial('partials/academy-charts', ['part' => 'kpis', 'overview' => $o]) ?>

        <div class="ad-grid">
            <section class="card ad-panel"><div class="card__body">
                <header class="ad-panel__head"><h2 class="ad-panel__title"><?= icon('layers', 'icon--sm') ?> Learners by topic</h2><a class="btn btn--outline btn--sm" href="<?= e($exp('topics')) ?>"><?= icon('download', 'icon--sm') ?> CSV</a></header>
                <p class="ad-panel__sub">A topic is the category of a course. A learner counts once per topic.</p>
                <?= partial('partials/academy-charts', ['part' => 'topics', 'topics' => $d['topics']]) ?>
                <?php if ($d['topics']): ?>
                    <div class="table-wrap"><table class="table table--compact">
                        <caption class="visually-hidden">Learners by topic</caption>
                        <thead><tr><th scope="col">Topic</th><th scope="col" class="num">Courses</th><th scope="col" class="num">Learners</th><th scope="col" class="num">Enrolments</th><th scope="col" class="num">Completed</th><th scope="col" class="num">Rate</th></tr></thead>
                        <tbody><?php foreach ($d['topics'] as $t): ?>
                            <tr><th scope="row"><?= e($t['label']) ?></th><td class="num"><?= (int) $t['courses'] ?></td><td class="num"><?= (int) $t['learners'] ?></td><td class="num"><?= (int) $t['enrollments'] ?></td><td class="num"><?= (int) $t['completed'] ?></td><td class="num"><?= rtrim(rtrim(number_format((float) $t['completionPct'], 1), '0'), '.') ?>%</td></tr>
                        <?php endforeach; ?></tbody>
                    </table></div>
                <?php endif; ?>
            </div></section>

            <section class="card ad-panel"><div class="card__body">
                <header class="ad-panel__head"><h2 class="ad-panel__title"><?= icon('globe', 'icon--sm') ?> Learners by country</h2><a class="btn btn--outline btn--sm" href="<?= e($exp('countries')) ?>"><?= icon('download', 'icon--sm') ?> CSV</a></header>
                <p class="ad-panel__sub">The country each active learner gave at sign-up (<?= (int) $d['countries']['total'] ?> learners).</p>
                <?= partial('partials/academy-charts', ['part' => 'countries', 'countries' => $d['countries']]) ?>
            </div></section>

            <section class="card ad-panel ad-panel--wide"><div class="card__body">
                <h2 class="ad-panel__title"><?= icon('layers', 'icon--sm') ?> Topics by country</h2>
                <p class="ad-panel__sub">How many learners of the biggest countries joined each topic.</p>
                <?php if (!$d['matrix']['rows']): ?><p class="ad-empty">Nothing to show until learners with a country join courses.</p><?php else: ?>
                    <div class="table-wrap"><table class="table table--compact">
                        <caption class="visually-hidden">Learners by topic and country</caption>
                        <thead><tr><th scope="col">Topic</th><?php foreach ($d['matrix']['countries'] as $c): ?><th scope="col" class="num"><?= e($c) ?></th><?php endforeach; ?></tr></thead>
                        <tbody><?php foreach ($d['matrix']['rows'] as $r): $mx = max(1, ...$r['cells']); ?>
                            <tr><th scope="row"><?= e($r['label']) ?></th><?php foreach ($r['cells'] as $n): ?><td class="num ad-heat" style="--a:<?= round($n / $mx * 0.55, 2) ?>"><?= $n ?: '·' ?></td><?php endforeach; ?></tr>
                        <?php endforeach; ?></tbody>
                    </table></div>
                <?php endif; ?>
            </div></section>

            <section class="card ad-panel"><div class="card__body">
                <header class="ad-panel__head"><h2 class="ad-panel__title"><?= icon('trending-up', 'icon--sm') ?> Growth, month by month</h2><a class="btn btn--outline btn--sm" href="<?= e($exp('months')) ?>"><?= icon('download', 'icon--sm') ?> CSV</a></header>
                <p class="ad-panel__sub">New learners and certificates over the last 12 months.</p>
                <?= partial('partials/academy-charts', ['part' => 'months', 'months' => $d['months']]) ?>
            </div></section>

            <section class="card ad-panel"><div class="card__body">
                <h2 class="ad-panel__title"><?= icon('award', 'icon--sm') ?> From sign-up to certificate</h2>
                <p class="ad-panel__sub">Each learner is counted once at every step. Completion rate of all enrolments: <strong><?= rtrim(rtrim(number_format((float) ($o['completionPct'] ?? 0), 1), '0'), '.') ?>%</strong>.</p>
                <?= partial('partials/academy-charts', ['part' => 'funnel', 'funnel' => $d['funnel']]) ?>
            </div></section>
        </div>
    </div>
<?php endif; ?>
