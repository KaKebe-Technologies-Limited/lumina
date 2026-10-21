<?php
/** Volunteer facilitator application. Vars: $topics (key => label), $learner. Copy: content('academy', 'facilitate.*'). */
$req = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) content('academy', 'facilitate.requirements')) ?: [])));
?>
<?= partial('partials/page-hero', ['title' => content('academy', 'facilitate.page_title'), 'eyebrow' => 'LAF Academy', 'lead' => content('academy', 'facilitate.page_lead')]) ?>
<section class="section ac-auth">
    <div class="container container--narrow">
        <p class="opp-note ac-note-box"><?= icon('heart', 'icon--sm') ?> <?= e(content('academy', 'facilitate.voluntary')) ?></p>
        <?php if ($req): ?>
            <h2 class="ac-block__title">What to send</h2>
            <ul class="ac-list"><?php foreach ($req as $r): ?><li><?= e($r) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <form class="ac-form card" method="post" action="<?= e(url('academy/facilitate')) ?>" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?><?= honeypot_field() ?>
            <div class="card__body">
                <?php if (error('form')): ?><p class="opp-note opp-note--warn"><?= e((string) error('form')) ?></p><?php endif; ?>
                <h2 class="ac-block__title">About you</h2>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'maxlength' => 120, 'autocomplete' => 'name']) ?>
                    <?= partial('partials/form-field', ['name' => 'email', 'label' => 'E-mail address', 'type' => 'email', 'required' => true, 'maxlength' => 190, 'autocomplete' => 'email', 'help' => 'We write to you here with the decision.']) ?>
                    <?= partial('partials/form-field', ['name' => 'phone', 'label' => 'Phone or WhatsApp (optional)', 'type' => 'tel', 'maxlength' => 40, 'autocomplete' => 'tel']) ?>
                    <?= partial('partials/form-field', ['name' => 'country', 'label' => 'Country (optional)', 'maxlength' => 80, 'autocomplete' => 'country-name']) ?>
                    <?= partial('partials/form-field', ['name' => 'headline', 'label' => 'Current role and organisation (optional)', 'maxlength' => 190, 'class' => 'field--full', 'help' => 'For example: Software developer at a bank, or ICT teacher at a secondary school.']) ?>
                </div>
                <h2 class="ac-block__title">What you would facilitate</h2>
                <div class="form-grid">
                    <?= partial('partials/form-field', ['name' => 'topic', 'label' => 'Topic', 'type' => 'select', 'options' => $topics, 'empty' => 'Choose a topic', 'required' => true, 'help' => 'You are approved for one topic. You can apply again for another.']) ?>
                    <?= partial('partials/form-field', ['name' => 'topic_detail', 'label' => 'The particular subject', 'required' => true, 'maxlength' => 190, 'help' => 'For example: Excel formulas and dashboards.']) ?>
                    <?= partial('partials/form-field', ['name' => 'experience', 'label' => 'Your experience and qualifications', 'type' => 'textarea', 'rows' => 6, 'required' => true, 'maxlength' => 3000, 'class' => 'field--full', 'help' => 'Years of experience, where you worked or taught, projects, certifications.']) ?>
                    <?= partial('partials/form-field', ['name' => 'motivation', 'label' => 'Why do you want to volunteer?', 'type' => 'textarea', 'rows' => 4, 'required' => true, 'maxlength' => 2000, 'class' => 'field--full']) ?>
                    <?= partial('partials/form-field', ['name' => 'availability', 'label' => 'When can you help? (optional)', 'maxlength' => 190, 'help' => 'For example: Saturdays 10:00 to 12:00 (EAT).']) ?>
                    <?= partial('partials/form-field', ['name' => 'links', 'label' => 'Profile or portfolio links (optional)', 'maxlength' => 500, 'help' => 'LinkedIn, GitHub or a website. Separate with spaces.']) ?>
                </div>
                <h2 class="ac-block__title">Your CV and papers</h2>
                <div class="field<?= error('cv') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-cv">CV (required)</label>
                    <input class="input" type="file" id="f-cv" name="cv" accept=".pdf,.doc,.docx" required aria-describedby="f-cv-help">
                    <div class="field__help" id="f-cv-help">PDF or Word, up to 5 MB.</div>
                    <?php if (error('cv')): ?><div class="field__error"><?= e((string) error('cv')) ?></div><?php endif; ?>
                </div>
                <div class="field<?= error('papers') ? ' field--error' : '' ?>">
                    <label class="field__label" for="f-papers">Supporting papers (certificates, degrees, letters)</label>
                    <input class="input" type="file" id="f-papers" name="papers[]" accept=".pdf,.doc,.docx" multiple aria-describedby="f-papers-help">
                    <div class="field__help" id="f-papers-help">Up to 4 files, PDF or Word, 5 MB each. Choose them together. Only our review team can open your files.</div>
                    <?php if (error('papers')): ?><div class="field__error"><?= e((string) error('papers')) ?></div><?php endif; ?>
                </div>
                <div class="field<?= error('volunteer') ? ' field--error' : '' ?>">
                    <label class="check"><input type="checkbox" name="volunteer" value="1"<?= old('volunteer') ? ' checked' : '' ?> required><span>I understand that facilitating is a voluntary role and is not paid.</span></label>
                    <?php if (error('volunteer')): ?><div class="field__error"><?= e((string) error('volunteer')) ?></div><?php endif; ?>
                </div>
                <div class="field<?= error('consent') ? ' field--error' : '' ?>">
                    <label class="check"><input type="checkbox" name="consent" value="1"<?= old('consent') ? ' checked' : '' ?> required><span>I agree that Lumina Africa Foundation may keep and review my CV and papers to decide on this application.</span></label>
                    <?php if (error('consent')): ?><div class="field__error"><?= e((string) error('consent')) ?></div><?php endif; ?>
                </div>
                <button class="btn btn--cta btn--caps btn--block" type="submit">Send my application</button>
            </div>
        </form>
    </div>
</section>
