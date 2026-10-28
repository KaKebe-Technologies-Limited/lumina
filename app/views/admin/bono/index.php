<?php
/** Admin > Bono AI. Vars: $ready, $stats, $taught, $open, $recent, $yes, $no, $ai (Claude phrases answers), $aiKey, $model, $on, $prefill, $prefillLog. */
$outcomes = [0 => 'Could not answer', 1 => 'From the website', 2 => 'Taught answer', 3 => 'Phrased by Claude', 4 => 'Small talk'];
?>
<?= partial('partials/admin/page-header', ['title' => 'Bono AI', 'subtitle' => 'The support assistant in the corner of every public page. It reads the whole website every day and answers visitors from it. Teach it what it does not know yet.',
    'actions' => [['label' => 'Settings', 'url' => url('admin/settings#tab-bono'), 'icon' => 'settings', 'class' => 'btn--outline']]]) ?>
<?php if (!$ready): ?>
    <div class="alert alert--warning" role="status"><strong>Bono AI needs the database update.</strong> Import database/lumina-live-import.sql (phpMyAdmin) once; it is safe to repeat.</div>
<?php else: ?>
    <div class="alert alert--info" role="status">
        <strong><?= $on ? 'Bono AI is switched on.' : 'Bono AI is switched off (Settings > Bono AI).' ?></strong>
        It has learned <strong><?= (int) $stats['pages'] ?></strong> pages (<?= (int) $stats['passages'] ?> passages)<?= $stats['last'] ? ', last on ' . e(format_datetime($stats['last'], 'j M Y, H:i')) : ', and has not learned the website yet: press the button below' ?>.
        <?php if ($ai): ?>Answers are phrased by Claude (<?= e($model) ?>), using only what the website says.<?php elseif ($aiKey): ?>Claude is switched off in Settings: Bono answers with the sentences of the website.<?php else: ?>Bono answers with the best sentences of the website. To let Claude phrase the answers, add <code>ANTHROPIC_API_KEY</code> to the .env file (docs/BONO.md).<?php endif; ?>
        <div class="aac-actions">
            <form method="post" action="<?= e(url('admin/bono/learn')) ?>" class="inline-form" data-confirm="Read the whole website now? It takes up to a minute."><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit" data-loading-text="Learning...">Learn the website now</button></form>
            <form method="post" action="<?= e(url('admin/bono/learn')) ?>" class="inline-form" data-confirm="Read every page again, even those that did not change? It takes up to a minute."><?= csrf_field() ?><input type="hidden" name="force" value="1"><button class="btn btn--outline btn--sm" type="submit" data-loading-text="Learning...">Read everything again</button></form>
        </div>
        <p class="text-muted">Every night: add the cron job <code>30 2 * * * /usr/bin/php /home/ACCOUNT/domains/DOMAIN/public_html/bin/bono-learn.php</code> (Hostinger > Advanced > Cron Jobs). New pages, news and courses are learned the next night.</p>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Questions Bono could not answer (<?= (int) $stats['unanswered'] ?>)</h2></div>
        <div class="card__body">
            <?php if ($open === []): ?><p class="text-muted">Nothing waiting. When a visitor asks something the website does not cover, it appears here so you can teach Bono the answer.</p><?php else: ?>
            <div class="table-wrap"><table class="table table--stack">
                <caption class="visually-hidden">Questions Bono could not answer</caption>
                <thead><tr><th scope="col">Question</th><th scope="col">Asked</th><th scope="col">Last</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($open as $o): ?>
                    <tr>
                        <td class="cell-main" data-label="Question"><?= e($o['question']) ?></td>
                        <td data-label="Asked"><?= (int) $o['times'] ?>x</td>
                        <td data-label="Last"><?= e(format_date($o['last_at'], 'j M')) ?></td>
                        <td data-label="Actions" class="nowrap">
                            <a class="btn btn--primary btn--sm" href="<?= e(url('admin/bono', ['q' => $o['question'], 'log' => $o['id']])) ?>#teach">Teach the answer</a>
                            <form method="post" action="<?= e(url('admin/bono/dismiss/' . (int) $o['id'])) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Dismiss</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" id="teach">
        <div class="card__header"><h2 class="card__title">Teach Bono an answer</h2></div>
        <div class="card__body">
            <form method="post" action="<?= e(url('admin/bono/teach')) ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="log" value="<?= (int) $prefillLog ?>">
                <div class="field<?= error('question') ? ' field--error' : '' ?>">
                    <label class="field__label" for="bt-q">The question, as a visitor would ask it</label>
                    <input class="input" id="bt-q" name="question" maxlength="300" value="<?= e((string) old('question', $prefill)) ?>" placeholder="How long does Academy approval take?" required>
                    <?php if (error('question')): ?><div class="field__error" role="alert"><?= e((string) error('question')) ?></div><?php endif; ?>
                </div>
                <div class="field">
                    <label class="field__label" for="bt-a">The answer Bono should give</label>
                    <textarea class="textarea" id="bt-a" name="answer" rows="4" maxlength="1500" required><?= e((string) old('answer', '')) ?></textarea>
                    <div class="field__help">Plain text, short and friendly. A taught answer always beats what the website says, whenever the words of the question match.</div>
                </div>
                <button class="btn btn--primary" type="submit">Teach Bono</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Taught answers (<?= count($taught) ?>)</h2></div>
        <div class="card__body">
            <?php if ($taught === []): ?><p class="text-muted">None yet.</p><?php else: ?>
            <div class="table-wrap"><table class="table table--stack">
                <caption class="visually-hidden">Taught answers</caption>
                <thead><tr><th scope="col">Question</th><th scope="col">Answer</th><th scope="col">Used</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($taught as $t): ?>
                    <tr class="<?= (int) $t['is_active'] === 1 ? '' : 'is-muted' ?>">
                        <td class="cell-main" data-label="Question"><?= e($t['question']) ?><?php if ((int) $t['is_active'] !== 1): ?> <span class="badge badge--neutral">off</span><?php endif; ?></td>
                        <td data-label="Answer"><?= e(mb_strimwidth((string) $t['answer'], 0, 160, '...')) ?></td>
                        <td data-label="Used"><?= (int) $t['hits'] ?>x</td>
                        <td data-label="Actions" class="nowrap">
                            <form method="post" action="<?= e(url('admin/bono/taught/' . (int) $t['id'] . '/toggle')) ?>" class="inline-form"><?= csrf_field() ?><button class="btn btn--outline btn--sm" type="submit"><?= (int) $t['is_active'] === 1 ? 'Switch off' : 'Switch on' ?></button></form>
                            <form method="post" action="<?= e(url('admin/bono/taught/' . (int) $t['id'] . '/delete')) ?>" class="inline-form" data-confirm="Remove this answer?"><?= csrf_field() ?><button class="btn btn--ghost btn--sm btn-danger-hover" type="submit">Remove</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card__header"><h2 class="card__title">Latest questions</h2></div>
        <div class="card__body">
            <p class="text-muted">Last 30 days: <strong><?= (int) $yes ?></strong> visitors said the answer helped, <strong><?= (int) $no ?></strong> said it did not. Questions are kept without any name, address or IP for 6 months.</p>
            <?php if ($recent === []): ?><p class="text-muted">No question yet.</p><?php else: ?>
            <div class="table-wrap"><table class="table table--stack">
                <caption class="visually-hidden">Latest questions</caption>
                <thead><tr><th scope="col">Question</th><th scope="col">How it was answered</th><th scope="col">Helpful</th><th scope="col">When</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td class="cell-main" data-label="Question"><?= e($r['question']) ?></td>
                        <td data-label="Answered"><span class="badge badge--<?= (int) $r['outcome'] === 0 ? 'warning' : 'success' ?>"><?= e($outcomes[(int) $r['outcome']] ?? '') ?></span></td>
                        <td data-label="Helpful"><?= $r['helpful'] === null ? '-' : ((int) $r['helpful'] === 1 ? 'Yes' : 'No') ?></td>
                        <td data-label="When"><span class="nowrap"><?= e(format_datetime($r['created_at'], 'j M, H:i')) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
