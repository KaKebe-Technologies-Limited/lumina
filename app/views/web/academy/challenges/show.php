<?php
/**
 * A challenge. Vars: $c, $phase, $entry (the signed-in learner's entry|null), $canApply, $board (ranked entries once final), $criteria, $entries, $questionCount, $cert,
 * $canQuiz, $canProject, $learner. Copy: content('academy', 'challenges.*').
 */
use App\Services\Academy\ChallengeService;
use App\Services\Academy\CourseService;

$lines = static fn(?string $t): array => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $t) ?: [])));
$paras = static fn(?string $t): array => array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', trim((string) $t)) ?: [])));
$when = static fn(?string $d): string => $d ? format_datetime($d, 'l j F Y, H:i') . ' EAT' : '';
$closes = ChallengeService::applyDeadline($c);
$team = (int) $c['team_max'] > 1;
$isProject = $c['kind'] !== 'quiz';
?>
<?= partial('partials/page-hero', ['title' => $c['title'], 'eyebrow' => ChallengeService::KINDS[$c['kind']] . ' - ' . CourseService::categoryLabel($c['category']), 'lead' => (string) $c['summary']]) ?>
<section class="section">
    <div class="container container--narrow">
        <p class="opp-notice ac-ok" role="status"><?= icon('info', 'icon--sm') ?> <strong><?= e(ChallengeService::PHASES[$phase] ?? '') ?></strong>
            <?php if ($phase === 'upcoming' && !empty($c['apply_opens_at'])): ?> Applications open <?= e($when($c['apply_opens_at'])) ?>.
            <?php elseif ($phase === 'applications' && $closes): ?> Apply before <?= e($when(date('Y-m-d H:i:s', $closes))) ?>.
            <?php elseif ($phase === 'live' && !empty($c['ends_at'])): ?> Ends <?= e($when($c['ends_at'])) ?>.
            <?php elseif ($phase === 'judging'): ?> The entries are being marked. Results are published automatically.
            <?php elseif ($phase === 'finished'): ?> The results are final.<?php endif; ?></p>

        <div id="entry" class="ac-entry card">
            <div class="card__body">
                <h2 class="ac-block__title">Your entry</h2>
                <?php if ($entry !== null && $entry['status'] !== 'withdrawn'): ?>
                    <p><strong><?= e(ChallengeService::ENTRY_STATUS[$entry['status']] ?? '') ?></strong><?php if (!empty($entry['team_name'])): ?> &middot; Team <?= e($entry['team_name']) ?><?php endif; ?></p>
                    <?php if ($entry['status'] === 'rejected' && !empty($entry['admin_note'])): ?><p class="text-muted"><?= e($entry['admin_note']) ?></p><?php endif; ?>

                    <?php if ($entry['status'] === 'approved' && $c['kind'] === 'quiz'): ?>
                        <?php if ($entry['quiz_submitted_at'] !== null): ?>
                            <p>Your quiz is submitted: <strong><?= (int) $entry['quiz_correct'] ?> of <?= (int) $entry['quiz_total'] ?></strong> correct (<?= e(rtrim(rtrim(number_format((float) $entry['score'], 1), '0'), '.')) ?>%) in <?= e(academy_duration((int) ceil(((int) $entry['quiz_seconds']) / 60))) ?>. Rankings are published when the challenge ends.</p>
                        <?php elseif ($canQuiz && $entry['quiz_started_at'] !== null): ?>
                            <p>You started the quiz. The clock is running.</p>
                            <p><a class="btn btn--cta btn--caps" href="<?= e(url('academy/challenges/' . $c['slug'] . '/quiz')) ?>">Continue the quiz</a></p>
                        <?php elseif ($canQuiz): ?>
                            <p>The quiz has <?= (int) $questionCount ?> questions<?php if ((int) $c['quiz_minutes'] > 0): ?> and <?= (int) $c['quiz_minutes'] ?> minutes. The clock starts when you press the button, and you have one attempt<?php endif; ?>.</p>
                            <form method="post" action="<?= e(url('academy/challenges/' . $c['slug'] . '/quiz/start')) ?>"><?= csrf_field() ?><button class="btn btn--cta btn--caps" type="submit" data-confirm="Start the quiz now? The clock starts immediately and you have one attempt.">Start the quiz</button></form>
                        <?php elseif ($phase === 'finished' || $phase === 'judging'): ?>
                            <p class="text-muted">You did not complete the quiz before it closed.</p>
                        <?php else: ?>
                            <p class="text-muted">The quiz opens <?= e($when($c['starts_at'])) ?>. Come back then.</p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($entry['status'] === 'approved' && $isProject): ?>
                        <?php if (!empty($entry['idea_title'])): ?><p class="ac-note"><strong>Your idea:</strong> <?= e($entry['idea_title']) ?></p><?php endif; ?>
                        <?php if ($entry['submitted_at'] !== null): ?>
                            <p>Project submitted <?= e(format_datetime($entry['submitted_at'], 'j M Y, H:i')) ?>: <strong><?= e($entry['sub_title']) ?></strong>.</p>
                        <?php endif; ?>
                        <?php if ($canProject): ?>
                            <form class="ac-form" method="post" action="<?= e(url('academy/challenges/' . $c['slug'] . '/project')) ?>" enctype="multipart/form-data" novalidate>
                                <?= csrf_field() ?>
                                <?= partial('partials/form-field', ['name' => 'sub_title', 'label' => 'Project title', 'required' => true, 'maxlength' => 190, 'value' => old('sub_title', $entry['sub_title'] ?? $entry['idea_title'] ?? '')]) ?>
                                <?= partial('partials/form-field', ['name' => 'sub_summary', 'label' => 'Describe your project', 'type' => 'textarea', 'rows' => 6, 'required' => true, 'maxlength' => 4000, 'value' => old('sub_summary', $entry['sub_summary'] ?? ''), 'help' => 'The problem, your solution, how it works and who benefits. Judges read this first.']) ?>
                                <?= partial('partials/form-field', ['name' => 'sub_url', 'label' => 'Link to your project', 'type' => 'url', 'maxlength' => 480, 'value' => old('sub_url', $entry['sub_url'] ?? ''), 'help' => 'A demo, code repository, video or shared folder (https://). Make sure anyone with the link can open it.']) ?>
                                <div class="field<?= error('sub_file') ? ' field--error' : '' ?>">
                                    <label class="field__label" for="f-sub_file">Or upload a file (optional)</label>
                                    <input class="input" type="file" id="f-sub_file" name="sub_file" accept=".pdf,.doc,.docx,.ppt,.pptx">
                                    <div class="field__help">PDF, Word or PowerPoint, up to 10 MB<?= !empty($entry['sub_file_name']) ? '. Current file: ' . e($entry['sub_file_name']) : '' ?>.</div>
                                    <?php if (error('sub_file')): ?><div class="field__error"><?= e((string) error('sub_file')) ?></div><?php endif; ?>
                                </div>
                                <button class="btn btn--cta btn--caps" type="submit"><?= $entry['submitted_at'] !== null ? 'Update my submission' : 'Submit my project' ?></button>
                            </form>
                        <?php elseif ($entry['submitted_at'] === null && $phase !== 'finished' && $phase !== 'judging'): ?>
                            <p class="text-muted">Project submission opens <?= e($when($c['starts_at'])) ?>.</p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($phase === 'finished'): ?>
                        <?php if ($entry['rank_no'] !== null): ?>
                            <p class="ac-chres"><?= !empty($entry['is_winner']) ? icon('award', 'icon--sm') . ' <strong>You won ' . e((string) $entry['award']) . '!</strong>' : 'You finished <strong>' . e(ChallengeService::ordinal((int) $entry['rank_no'])) . '</strong>.' ?>
                                Score <?= e(rtrim(rtrim(number_format((float) $entry['score'], 1), '0'), '.')) ?>%.</p>
                        <?php endif; ?>
                        <?php if ($cert): ?><p><a class="btn btn--cta btn--caps" href="<?= e(url('academy/certificate/' . $cert['code'])) ?>"><?= icon('award', 'icon--sm') ?> Open my certificate</a></p><?php endif; ?>
                    <?php endif; ?>

                    <?php if ($phase !== 'finished' && $phase !== 'judging'): ?>
                        <form class="ac-form ac-form--inline" method="post" action="<?= e(url('academy/challenges/' . $c['slug'] . '/photo')) ?>" enctype="multipart/form-data" novalidate>
                            <?= csrf_field() ?>
                            <h3 class="ac-block__title">Winner photo</h3>
                            <p class="text-muted"><?= e(content('academy', 'challenges.photo_note')) ?></p>
                            <div class="field<?= error('photo') ? ' field--error' : '' ?>">
                                <label class="field__label" for="f-photo"><?= !empty($entry['photo_path']) ? 'Replace my photo' : 'Add my photo' ?></label>
                                <input class="input" type="file" id="f-photo" name="photo" accept="image/jpeg,image/png,image/webp">
                                <?php if (error('photo')): ?><div class="field__error"><?= e((string) error('photo')) ?></div><?php endif; ?>
                            </div>
                            <div class="field<?= error('consent') ? ' field--error' : '' ?>"><label class="check"><input type="checkbox" name="consent" value="1"<?= !empty($entry['photo_consent_at']) || old('consent') ? ' checked' : '' ?>><span>If I win, my name, photo and result may be published.</span></label>
                                <?php if (error('consent')): ?><div class="field__error"><?= e((string) error('consent')) ?></div><?php endif; ?></div>
                            <button class="btn btn--outline btn--caps btn--sm" type="submit">Save</button>
                        </form>
                        <?php if ($entry['quiz_started_at'] === null): ?>
                            <form method="post" action="<?= e(url('academy/challenges/' . $c['slug'] . '/withdraw')) ?>" data-confirm="Withdraw your entry?"><?= csrf_field() ?><button class="btn btn--ghost btn--sm" type="submit">Withdraw my entry</button></form>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php elseif ($learner === null): ?>
                    <?php if ($canApply): ?>
                        <p>Create a free account or sign in to apply.</p>
                        <p><a class="btn btn--cta btn--caps" href="<?= e(url('academy/login', ['next' => 'academy/challenges/' . $c['slug']])) ?>">Sign in to apply</a>
                            <a class="btn btn--outline btn--caps" href="<?= e(url('academy/register', ['next' => 'academy/challenges/' . $c['slug']])) ?>">Create an account</a></p>
                    <?php else: ?><p class="text-muted">Applications are not open.</p><?php endif; ?>
                <?php elseif ($canApply): ?>
                    <form class="ac-form" method="post" action="<?= e(url('academy/challenges/' . $c['slug'] . '/apply')) ?>" enctype="multipart/form-data" id="apply" novalidate>
                        <?= csrf_field() ?>
                        <?php if (error('form')): ?><p class="opp-note opp-note--warn"><?= e((string) error('form')) ?></p><?php endif; ?>
                        <?php if ($team): ?>
                            <?= partial('partials/form-field', ['name' => 'team_name', 'label' => 'Team name (leave empty to enter alone)', 'maxlength' => 120]) ?>
                            <?= partial('partials/form-field', ['name' => 'members', 'label' => 'Your team mates', 'maxlength' => 600, 'help' => 'Their names separated by commas. A team has at most ' . (int) $c['team_max'] . ' people including you.']) ?>
                        <?php endif; ?>
                        <?php if ($isProject): ?>
                            <?= partial('partials/form-field', ['name' => 'idea_title', 'label' => 'Your idea in a few words', 'required' => true, 'maxlength' => 190]) ?>
                            <?= partial('partials/form-field', ['name' => 'idea_summary', 'label' => 'What do you want to build and why?', 'type' => 'textarea', 'rows' => 5, 'required' => true, 'maxlength' => 2000]) ?>
                        <?php endif; ?>
                        <div class="field<?= error('photo') ? ' field--error' : '' ?>">
                            <label class="field__label" for="f-apply-photo">Your photo (optional)</label>
                            <input class="input" type="file" id="f-apply-photo" name="photo" accept="image/jpeg,image/png,image/webp">
                            <div class="field__help"><?= e(content('academy', 'challenges.photo_note')) ?></div>
                            <?php if (error('photo')): ?><div class="field__error"><?= e((string) error('photo')) ?></div><?php endif; ?>
                        </div>
                        <div class="field<?= error('consent') ? ' field--error' : '' ?>"><label class="check"><input type="checkbox" name="consent" value="1"<?= old('consent') ? ' checked' : '' ?>><span>If I win, my name, photo and result may be published.</span></label>
                            <?php if (error('consent')): ?><div class="field__error"><?= e((string) error('consent')) ?></div><?php endif; ?></div>
                        <button class="btn btn--cta btn--caps" type="submit">Apply to this challenge</button>
                        <?php if ($c['approval'] === 'manual'): ?><p class="text-muted">Entries are approved by our team before you can take part.</p><?php endif; ?>
                    </form>
                <?php else: ?>
                    <p class="text-muted"><?= $entry !== null ? 'You withdrew from this challenge.' : 'Applications are not open for this challenge.' ?></p>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="ac-block__title">About this challenge</h2>
        <?php foreach ($paras($c['description']) as $p): ?><p><?= nl2br(e($p)) ?></p><?php endforeach; ?>

        <h2 class="ac-block__title">Key dates (East Africa Time)</h2>
        <ul class="ac-list">
            <?php if (!empty($c['apply_opens_at'])): ?><li>Applications open: <?= e($when($c['apply_opens_at'])) ?></li><?php endif; ?>
            <?php if ($closes): ?><li>Applications close: <?= e($when(date('Y-m-d H:i:s', $closes))) ?></li><?php endif; ?>
            <?php if (!empty($c['starts_at'])): ?><li>Starts: <?= e($when($c['starts_at'])) ?></li><?php endif; ?>
            <?php if (!empty($c['ends_at'])): ?><li>Ends: <?= e($when($c['ends_at'])) ?></li><?php endif; ?>
            <?php if ($c['scoring'] === 'judged' && !empty($c['judging_ends_at'])): ?><li>Results final: <?= e($when($c['judging_ends_at'])) ?></li><?php endif; ?>
        </ul>

        <?php if ($lines($c['rules'])): ?>
            <h2 class="ac-block__title">Rules</h2>
            <ul class="ac-list"><?php foreach ($lines($c['rules']) as $r): ?><li><?= e($r) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if ($criteria): ?>
            <h2 class="ac-block__title">How projects are scored</h2>
            <p>Each judge gives points for every criterion and the system averages the judges' scores. The highest total wins.</p>
            <ul class="ac-list"><?php foreach ($criteria as $x): ?><li><?= e($x['name']) ?>: <?= (int) $x['weight'] ?>%</li><?php endforeach; ?></ul>
        <?php elseif ($c['kind'] === 'quiz'): ?>
            <h2 class="ac-block__title">How the winners are chosen</h2>
            <p>Your answers are marked by the system. The highest score wins and the fastest finishing time breaks a tie. The top <?= (int) $c['winners_count'] ?> win.</p>
        <?php endif; ?>
        <?php if (trim((string) $c['prize']) !== ''): ?>
            <h2 class="ac-block__title">Prize</h2>
            <p><?= icon('award', 'icon--sm') ?> <?= e($c['prize']) ?></p>
        <?php endif; ?>

        <?php if ($board): ?>
            <h2 class="ac-block__title" id="results">Results</h2>
            <div class="table-wrap"><table class="table table--stack">
                <caption class="visually-hidden">Ranking</caption>
                <thead><tr><th scope="col">Rank</th><th scope="col">Name</th><th scope="col">Score</th><th scope="col">Award</th></tr></thead>
                <tbody>
                <?php foreach ($board as $b): ?>
                    <tr<?= !empty($b['is_winner']) ? ' class="is-winner"' : '' ?>>
                        <td data-label="Rank"><?= e(ChallengeService::ordinal((int) $b['rank_no'])) ?></td>
                        <td data-label="Name"><?= e($b['display_name']) ?></td>
                        <td data-label="Score"><?= e(rtrim(rtrim(number_format((float) $b['score'], 1), '0'), '.')) ?>%</td>
                        <td data-label="Award"><?= !empty($b['is_winner']) ? icon('award', 'icon--sm') . ' ' . e((string) $b['award']) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table></div>
        <?php endif; ?>
        <p class="ac-center"><a class="btn btn--outline btn--caps" href="<?= e(url('academy/challenges')) ?>">All challenges</a></p>
    </div>
</section>
