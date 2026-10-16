<?php
/** Vacancy page. Vars: $job (row), $closed (bool). Labels: content('careers', 'job.*') and content('careers', 'form.*'). */
$typeMap = ['full_time' => 'FULL_TIME', 'part_time' => 'PART_TIME', 'contract' => 'CONTRACTOR', 'internship' => 'INTERN', 'volunteer' => 'VOLUNTEER'];
$closing = !empty($job['closing_date']) ? format_date($job['closing_date']) : '';
$ld = array_filter([
    '@context'           => 'https://schema.org',
    '@type'              => 'JobPosting',
    'title'              => (string) $job['title'],
    'description'        => trim((string) strip_tags((string) ($job['description'] ?? ''))) !== '' ? rich($job['description']) : (string) ($job['summary'] ?? ''),
    'datePosted'         => to_datetime($job['created_at'] ?? null)?->format('Y-m-d'),
    'validThrough'       => !empty($job['closing_date']) ? format_date($job['closing_date'], 'Y-m-d') : null,
    'employmentType'     => $typeMap[$job['employment_type']] ?? null,
    'hiringOrganization' => ['@type' => 'Organization', 'name' => (string) setting('site_name', ''), 'sameAs' => abs_url(''), 'logo' => abs_url_for_image((string) setting('logo_path', 'assets/img/brand/logo-512.png'))],
    // a posting without its own location is based at the organisation's address (schema.org wants a place for on-site jobs)
    'jobLocation'        => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress',
        'addressLocality' => trim((string) ($job['location'] ?? '')) !== '' ? (string) $job['location'] : (string) setting('city_country', ''), 'addressCountry' => 'UG']],
]);
$this->start('head'); ?>
<?php if (!$closed): ?><script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script><?php endif; ?>
<?php $this->end(); $this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/careers.js')) ?>" defer></script>
<?php $this->end(); ?>

<?= partial('partials/page-hero', [
    'title'   => $job['title'],
    'eyebrow' => job_type_label($job['employment_type']),
    'lead'    => (string) ($job['summary'] ?? ''),
]) ?>

<section class="section job-page" aria-label="<?= e($job['title']) ?>">
    <div class="container">
        <div class="job-page__layout">
            <div class="job-page__main" data-reveal>
                <?php if (trim((string) ($job['description'] ?? '')) !== ''): ?>
                    <h2 class="job-page__heading"><?= e(content('careers', 'job.description_title')) ?></h2>
                    <div class="prose"><?= rich($job['description']) ?></div>
                <?php endif; ?>
                <?php if (trim((string) ($job['requirements'] ?? '')) !== ''): ?>
                    <h2 class="job-page__heading"><?= e(content('careers', 'job.requirements_title')) ?></h2>
                    <div class="prose"><?= rich($job['requirements']) ?></div>
                <?php endif; ?>
                <?php if (trim((string) ($job['how_to_apply'] ?? '')) !== '' || trim((string) ($job['apply_email'] ?? '')) !== ''): ?>
                    <h2 class="job-page__heading"><?= e(content('careers', 'job.how_title')) ?></h2>
                    <div class="prose">
                        <?= nl2p($job['how_to_apply'] ?? '') ?>
                        <?php if (trim((string) ($job['apply_email'] ?? '')) !== ''): ?>
                            <p><?= e(content('careers', 'job.email_label')) ?> <a href="mailto:<?= e($job['apply_email']) ?>"><?= e($job['apply_email']) ?></a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="ia-aside" aria-label="<?= e($job['title']) ?>" data-reveal>
                <dl class="ia-facts">
                    <div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('briefcase') ?></span><div><dt><?= e(content('careers', 'job.type_label')) ?></dt><dd><?= e(job_type_label($job['employment_type'])) ?></dd></div></div>
                    <?php if (!empty($job['department'])): ?><div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('layers') ?></span><div><dt><?= e(content('careers', 'job.department_label')) ?></dt><dd><?= e($job['department']) ?></dd></div></div><?php endif; ?>
                    <?php if (!empty($job['location'])): ?><div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('map-pin') ?></span><div><dt><?= e(content('careers', 'job.location_label')) ?></dt><dd><?= e($job['location']) ?></dd></div></div><?php endif; ?>
                    <?php if ($closing !== ''): ?><div class="ia-facts__row"><span class="ia-facts__icon" aria-hidden="true"><?= icon('calendar') ?></span><div><dt><?= e(content('careers', 'job.closing_label')) ?></dt><dd><time datetime="<?= e(format_date($job['closing_date'], 'Y-m-d')) ?>"><?= e($closing) ?></time></dd></div></div><?php endif; ?>
                </dl>
                <p><a class="btn btn--outline btn--caps btn--block" href="<?= e(url('careers')) ?>"><?= icon('arrow-left', 'icon--sm') ?> <?= e(content('careers', 'job.back_label')) ?></a></p>
            </aside>
        </div>
    </div>
</section>

<section class="section section--tint" id="apply" aria-labelledby="apply-title">
    <div class="container container--narrow">
        <?php if ($closed): ?>
            <div class="no-openings">
                <span class="no-openings__icon"><?= icon('briefcase', 'icon--xl') ?></span>
                <p class="no-openings__title" id="apply-title"><?= e(content('careers', 'job.closed_text')) ?></p>
                <p><a class="btn btn--cta btn--caps" href="<?= e(url('careers')) ?>#openings"><?= e(content('careers', 'job.closed_button')) ?></a></p>
            </div>
        <?php else: ?>
            <?= partial('partials/section-head', ['title' => (string) content('careers', 'job.apply_title'), 'align' => 'center', 'id' => 'apply-title']) ?>
            <?= partial('web/careers/_apply_form', [
                'action'      => url('careers/' . $job['slug'] . '/apply'),
                'idPrefix'    => 'job',
                'messageHelp' => content('careers', 'apply.message_help'),
                'submit'      => content('careers', 'form.submit_job'),
            ]) ?>
        <?php endif; ?>
    </div>
</section>
