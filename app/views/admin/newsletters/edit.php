<?php
/**
 * Admin > Newsletter > editor (EDITOR agent). Two panes: editing on the left, a live preview rendered by the SERVER on the right.
 * Behaviour: assets/js/admin-newsletter.js (autosave, block list, drag and drop, preview, publish...). Styles: scss/admin/_newsletter.scss.
 * Vars: $issue (row), $item (JSON shape of the API), $boot (data block for the script), $pages (link suggestions), $canSend, $canDeletePublished.
 * The block cards are built by the script from the <template> elements of _templates.php, using the issue's JSON content.
 * A PDF issue (kind = pdf) shows the PDF box first and hides the featured article and the sections (they stay in the page, hidden, so the script's
 * field access works and the stored content stays the empty structure). Every issue has the "Live on the website" box (what visitors see right now).
 */
$id = (int) $item['id'];
$urls = $boot['urls'];
$publicOnce = !empty($issue['published_at']);
$kind = (string) ($item['kind'] ?? 'online');                  // chosen when the issue was created: 'online' (block editor) or 'pdf' (an uploaded PDF)
$pdfIssue = $kind === 'pdf';
$this->start('scripts'); ?>
<script src="<?= e(asset('js/admin-editor.js')) ?>" defer></script>
<script src="<?= e(asset('js/admin-newsletter.js')) ?>" defer></script>
<?php if (can('social.share')): ?><script src="<?= e(asset('js/admin-social.js')) ?>" defer></script><?php endif; ?>
<?php $this->end(); ?>

<div class="nl-admin nl-editor" data-nl-editor data-status="<?= e($item['status']) ?>" data-kind="<?= e($kind) ?>">
    <script type="application/json" id="nl-boot"><?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR) ?></script>
    <noscript><div class="alert alert--error"><?= icon('alert-circle') ?><div class="alert__body">The newsletter editor needs JavaScript. Please turn it on in your browser, then reload this page.</div></div></noscript>

    <datalist id="nl-pages">
        <?php foreach ($pages as $p): ?><option value="<?= e($p['url']) ?>"><?= e($p['title']) ?></option><?php endforeach; ?>
    </datalist>

    <!-- ===================================================================== toolbar -->
    <div class="nl-bar" data-bar>
        <div class="nl-bar__main">
            <a class="nl-bar__back" href="<?= e($urls['list']) ?>"><?= icon('arrow-left', 'icon--sm') ?> <span>All issues</span></a>
            <div class="nl-bar__id">
                <h1 class="nl-bar__title" data-bar-title><?= e($item['title']) ?></h1>
                <span class="badge badge--neutral badge--dot" data-status-badge><?= e(\App\Services\Newsletter\NewsletterService::STATUS_LABELS[$item['status']] ?? $item['status']) ?></span>
                <span class="badge badge--info nl-fmt-badge" title="<?= $pdfIssue ? 'This issue is an uploaded PDF' : 'This issue is written in the editor' ?>"><?= $pdfIssue ? icon('file-text', 'icon--sm') . ' PDF issue' : 'Written online' ?></span>
            </div>
            <p class="nl-save" data-save data-state="saved" role="status" aria-live="polite"><span class="nl-save__dot" aria-hidden="true"></span><span data-save-text>Loading...</span></p>
        </div>
        <div class="nl-bar__actions" role="group" aria-label="Issue actions">
            <?php if (!$pdfIssue): ?><button type="button" class="btn btn--ghost btn--sm" data-act="history"><?= icon('history', 'icon--sm') ?> History</button><?php endif; /* a PDF issue has no written content to bring back */ ?>
            <button type="button" class="btn btn--outline btn--sm" data-act="preview" title="Opens the saved issue as visitors will see it, in a new tab"><?= icon('eye', 'icon--sm') ?> Preview in new tab</button>
            <?php if ($canSend): ?><a class="btn btn--outline btn--sm" href="<?= e($urls['email']) ?>" data-act="email"><?= icon('mail', 'icon--sm') ?> E-mail</a><?php endif; ?>
            <button type="button" class="btn btn--primary btn--sm" data-act="save"><?= icon('save', 'icon--sm') ?> <span data-save-label>Save draft</span></button>
            <button type="button" class="btn btn--outline btn--sm" data-act="schedule" hidden><?= icon('clock', 'icon--sm') ?> <span data-schedule-label>Schedule...</span></button>
            <button type="button" class="btn btn--cta btn--sm" data-act="publish" hidden><?= icon('send', 'icon--sm') ?> Publish now</button>
            <button type="button" class="btn btn--outline btn--sm" data-act="unpublish" hidden><?= icon('eye-off', 'icon--sm') ?> <span data-unpublish-label>Unpublish</span></button>
            <button type="button" class="btn btn--ghost btn--sm nl-btn-danger" data-act="delete"><?= icon('trash', 'icon--sm') ?> Delete</button>
        </div>
    </div>

    <div class="nl-notices">
        <div class="nl-banner" data-banner hidden></div>
        <div class="alert alert--error nl-errors" data-errors tabindex="-1" role="alert" hidden></div>
    </div>

    <?php /* ----------------------------------------------------------------- "Live on the website": the truth about what visitors see, refreshed after every change */ ?>
    <section class="nl-live" data-live data-tone="neutral" aria-labelledby="nl-live-title">
        <div class="nl-live__main">
            <span class="nl-live__icon" aria-hidden="true" data-live-icon></span>
            <div class="nl-live__text">
                <h2 class="nl-live__title" id="nl-live-title" data-live-title>Checking...</h2>
                <p class="nl-live__detail" data-live-detail></p>
                <ul class="nl-live__reasons" data-live-reasons></ul>
                <p class="nl-live__unsaved" data-live-unsaved hidden>You have changes that are not saved yet. Visitors see the last saved version.</p>
                <p class="nl-live__checked" data-live-checked role="status" aria-live="polite"></p>
            </div>
        </div>
        <div class="nl-live__actions">
            <a class="btn btn--outline btn--sm" href="#" target="_blank" rel="noopener" data-live-view><?= icon('external-link', 'icon--sm') ?> View on the website<span class="visually-hidden"> (opens in a new tab)</span></a>
            <a class="btn btn--ghost btn--sm" href="<?= e($urls['preview']) ?>" target="_blank" rel="noopener" data-live-preview><?= icon('eye', 'icon--sm') ?> Preview<span class="visually-hidden"> (opens in a new tab)</span></a>
            <button type="button" class="btn btn--ghost btn--sm" data-act="check-live"><?= icon('refresh', 'icon--sm') ?> Check the website now</button>
        </div>
    </section>

    <div class="nl-view-toggle" role="group" aria-label="Show">
        <button type="button" aria-pressed="true" data-view="edit"><?= icon('edit', 'icon--sm') ?> Write</button>
        <button type="button" aria-pressed="false" data-view="preview"><?= icon('eye', 'icon--sm') ?> Preview</button>
    </div>

    <div class="nl-split" data-split data-view="edit">
        <!-- ================================================================= left: editing -->
        <div class="nl-pane nl-pane--edit" id="nl-pane-edit" data-pane="edit">

            <section class="nl-card nl-check" data-check aria-labelledby="nl-check-title">
                <h2 class="nl-check__title" id="nl-check-title" data-check-title>Checking...</h2>
                <ul class="nl-check__list" data-check-list></ul>
            </section>

            <?php if ($pdfIssue): ?>
                <!-- ------------------------------------------------------------- the PDF (a PDF issue) -->
                <?= partial('admin/newsletters/_pdf', ['kind' => $kind, 'pdfLimits' => $boot['pdf']]) ?>
            <?php endif; ?>

            <!-- ------------------------------------------------------------- issue details -->
            <section class="nl-card is-open" data-card="details" id="nl-card-details" aria-labelledby="nl-details-title">
                <header class="nl-card__head">
                    <h2 class="nl-card__h" id="nl-details-title">
                        <button type="button" class="nl-card__toggle" data-collapse-toggle aria-expanded="true" aria-controls="nl-details-body">
                            <span class="nl-card__title">Issue details</span>
                            <span class="nl-card__sub"><?= $pdfIssue ? 'Title, number, date, summary and cover picture shown on the website' : 'Title, number, date, summary and cover picture' ?></span>
                            <?= icon('chevron-down', 'icon--sm nl-card__chev') ?>
                        </button>
                    </h2>
                </header>
                <div class="nl-card__body" id="nl-details-body">
                    <div class="field">
                        <label class="field__label" for="nl-title">Title <span class="field__req" aria-hidden="true">*</span></label>
                        <input class="input" type="text" id="nl-title" maxlength="190" autocomplete="off" data-f="title" data-count="190" required aria-describedby="nl-title-help">
                        <p class="field__help" id="nl-title-help">The headline of this issue. It appears on the website, in the archive and in e-mails.</p>
                    </div>
                    <div class="nl-grid-2">
                        <div class="field">
                            <label class="field__label" for="nl-number">Issue number</label>
                            <input class="input" type="number" id="nl-number" min="1" max="99999" step="1" inputmode="numeric" data-f="issue_number" aria-describedby="nl-number-help">
                            <p class="field__help" id="nl-number-help"><span data-number-hint>Each issue needs its own number.</span></p>
                        </div>
                        <div class="field">
                            <label class="field__label" for="nl-date">Issue date</label>
                            <input class="input" type="date" id="nl-date" data-f="issue_date" aria-describedby="nl-date-help">
                            <p class="field__help" id="nl-date-help">The date printed on the issue. Newer dates are listed first.</p>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field__label" for="nl-summary">Summary</label>
                        <textarea class="textarea" id="nl-summary" rows="3" maxlength="600" data-f="summary" data-count="600" aria-describedby="nl-summary-help"></textarea>
                        <p class="field__help" id="nl-summary-help">One or two sentences. Shown under the title, in the archive and when the issue is shared.</p>
                    </div>
                    <div class="nl-cover" data-cover>
                        <?= partial('admin/newsletters/_image', ['idp' => 'nl-cover', 'label' => 'Cover picture (optional)', 'caption' => false, 'altMax' => 190,
                            'help' => $pdfIssue
                                ? 'A picture of the first page or a cover design. It is shown next to the download button, on the archive card and when the issue is shared. JPG, PNG, GIF or WebP, up to 5 MB. You can also drop a picture on this box.'
                                : 'A wide picture shown at the top of the issue and on its archive card. JPG, PNG, GIF or WebP, up to 5 MB. You can also drop a picture on this box.']) ?>
                    </div>
                </div>
            </section>

            <?php /* a PDF issue has no written content: the next two blocks stay in the page (hidden) so the script's field access works unchanged */ ?>
            <div class="nl-online-only"<?= $pdfIssue ? ' hidden' : '' ?>>
            <!-- ------------------------------------------------------------- featured article -->
            <section class="nl-card is-open" data-card="featured" data-featured id="nl-card-featured" aria-labelledby="nl-featured-title-h">
                <header class="nl-card__head">
                    <h2 class="nl-card__h" id="nl-featured-title-h">
                        <button type="button" class="nl-card__toggle" data-collapse-toggle aria-expanded="true" aria-controls="nl-featured-body">
                            <span class="nl-card__title">Featured article</span>
                            <span class="nl-card__sub">The main story of this issue, shown first and large</span>
                            <?= icon('chevron-down', 'icon--sm nl-card__chev') ?>
                        </button>
                    </h2>
                </header>
                <div class="nl-card__body" id="nl-featured-body">
                    <div class="field">
                        <label class="field__label" for="nl-featured-heading">Heading</label>
                        <input class="input" type="text" id="nl-featured-heading" maxlength="200" autocomplete="off" data-f="heading" data-count="200">
                    </div>
                    <?= partial('admin/newsletters/_rich', ['idp' => 'nl-featured-text', 'label' => 'Text', 'f' => 'body_html', 'placeholder' => 'Write the main story here...']) ?>
                    <div class="nl-optional">
                        <h3 class="nl-optional__title">Picture <span>(optional)</span></h3>
                        <?= partial('admin/newsletters/_image', ['idp' => 'nl-featured-img', 'label' => 'Picture', 'caption' => true]) ?>
                    </div>
                    <div class="nl-optional">
                        <h3 class="nl-optional__title">Button under the story <span>(optional)</span></h3>
                        <?= partial('admin/newsletters/_link', ['idp' => 'nl-featured-link', 'textLabel' => 'Button text', 'urlLabel' => 'Link address']) ?>
                    </div>
                </div>
            </section>

            <!-- ------------------------------------------------------------- blocks -->
            <section class="nl-blocks-section" aria-labelledby="nl-blocks-title">
                <div class="nl-blocks-section__head">
                    <div>
                        <h2 class="nl-section-title" id="nl-blocks-title">More sections <span class="nl-count" data-block-count>0</span></h2>
                        <p class="nl-section-help">Add as many as you need, in any order. Drag the handle <span class="nl-inline-icon" aria-hidden="true"><?= icon('grip', 'icon--sm') ?></span> to move a block, or use the arrow buttons.</p>
                    </div>
                    <div class="nl-blocks-section__tools">
                        <button type="button" class="btn btn--ghost btn--sm" data-act="collapse-all">Collapse all</button>
                        <button type="button" class="btn btn--ghost btn--sm" data-act="expand-all">Expand all</button>
                    </div>
                </div>

                <div class="nl-blocks" data-blocks></div>

                <div class="nl-empty" data-blocks-empty hidden>
                    <?= icon('layers', 'icon--xl') ?>
                    <p><strong>No extra sections yet.</strong> The featured article above can be a complete issue on its own, or you can add more below.</p>
                </div>

                <div class="nl-add" data-add>
                    <h3 class="nl-add__title" id="nl-add-title">Add a section</h3>
                    <div class="nl-add__grid" role="group" aria-labelledby="nl-add-title">
                        <button type="button" class="nl-add__btn" data-add-block="article"><?= icon('file-text') ?><strong>Article</strong><span>Heading, text, picture, link</span></button>
                        <button type="button" class="nl-add__btn" data-add-block="image"><?= icon('image') ?><strong>Image</strong><span>A picture with a caption</span></button>
                        <button type="button" class="nl-add__btn" data-add-block="quote"><?= icon('quote') ?><strong>Quote</strong><span>A highlighted quotation</span></button>
                        <button type="button" class="nl-add__btn" data-add-block="button"><?= icon('arrow-up-right') ?><strong>Button</strong><span>A button that links somewhere</span></button>
                        <button type="button" class="nl-add__btn" data-add-block="divider"><?= icon('minus') ?><strong>Divider</strong><span>A thin separating line</span></button>
                    </div>
                    <p class="nl-add__limit" data-add-limit hidden>An issue can have at most <?= (int) $boot['limits']['blocks'] ?> sections. Remove one to add another.</p>
                </div>
            </section>
            </div><?php /* /.nl-online-only */ ?>

            <?php if (!$pdfIssue): ?>
                <!-- ------------------------------------------------------------- optional PDF download (a written issue) -->
                <?= partial('admin/newsletters/_pdf', ['kind' => $kind, 'pdfLimits' => $boot['pdf']]) ?>
            <?php endif; ?>

            <!-- ------------------------------------------------------------- search engines -->
            <section class="nl-card" data-card="seo" id="nl-card-seo" aria-labelledby="nl-seo-card-title">
                <header class="nl-card__head">
                    <h2 class="nl-card__h" id="nl-seo-card-title">
                        <button type="button" class="nl-card__toggle" data-collapse-toggle aria-expanded="false" aria-controls="nl-seo-body">
                            <span class="nl-card__title">Search engines and sharing</span>
                            <span class="nl-card__sub">Web address and how this issue looks in Google</span>
                            <?= icon('chevron-down', 'icon--sm nl-card__chev') ?>
                        </button>
                    </h2>
                </header>
                <div class="nl-card__body" id="nl-seo-body" hidden>
                    <div class="field">
                        <label class="field__label" for="nl-slug">Web address of this issue</label>
                        <div class="nl-slug">
                            <span class="nl-slug__base" data-slug-base></span>
                            <input class="input" type="text" id="nl-slug" maxlength="160" autocomplete="off" spellcheck="false" data-f="slug" aria-describedby="nl-slug-help"<?= $publicOnce ? ' readonly' : '' ?>>
                        </div>
                        <p class="field__help" id="nl-slug-help"><?= $publicOnce ? 'This issue has been public, so its web address is locked: links people already have keep working.' : 'Made from the title. Use lower-case letters, numbers and hyphens. It is locked once the issue is published.' ?></p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="nl-seo-title">Title for search engines</label>
                        <input class="input" type="text" id="nl-seo-title" maxlength="190" autocomplete="off" data-f="seo_title" data-count="190" aria-describedby="nl-seo-title-help">
                        <p class="field__help" id="nl-seo-title-help">Leave empty to use the issue title. Around 60 characters works best.</p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="nl-seo-desc">Description for search engines</label>
                        <textarea class="textarea" id="nl-seo-desc" rows="3" maxlength="300" data-f="seo_description" data-count="300" aria-describedby="nl-seo-desc-help"></textarea>
                        <p class="field__help" id="nl-seo-desc-help">Leave empty to use the summary. Around 155 characters works best.</p>
                    </div>
                    <div class="nl-serp" aria-label="How it may look in search results">
                        <p class="nl-serp__label">How it may look in Google</p>
                        <p class="nl-serp__title" data-serp-title></p>
                        <p class="nl-serp__url" data-serp-url></p>
                        <p class="nl-serp__desc" data-serp-desc></p>
                    </div>
                </div>
            </section>

            <?php if (can('social.share')): ?>
            <!-- ------------------------------------------------------------- share on social media (docs/SOCIAL.md; the panel is built by assets/js/admin-social.js from GET <?= e('admin/api/newsletters/{id}/share') ?>) -->
            <section class="nl-card is-open" data-card="share" id="nl-card-share" aria-labelledby="nl-share-title" data-social-panel data-issue-id="<?= $id ?>"
                     data-api="<?= e(url('admin/api/newsletters/' . $id . '/share')) ?>" data-kit-url="<?= e(url('admin/social/kit/' . $id)) ?>" data-log-url="<?= e(url('admin/social/log?issue=' . $id)) ?>"
                     data-channels-url="<?= can('social.manage') ? e(url('admin/social/channels')) : '' ?>">
                <header class="nl-card__head nl-card__head--plain"><h2 class="nl-card__h" id="nl-share-title"><span class="nl-card__title">Share on social media</span></h2></header>
                <div class="nl-card__body" data-social-panel-body>
                    <p data-social-panel-loading>Loading...</p>
                    <noscript><p>The share panel needs JavaScript. You can still use the <a href="<?= e(url('admin/social/kit/' . $id)) ?>">Share kit</a>.</p></noscript>
                </div>
            </section>
            <?php endif; ?>

            <!-- ------------------------------------------------------------- issue actions -->
            <section class="nl-card" data-card="actions" aria-labelledby="nl-actions-title">
                <header class="nl-card__head nl-card__head--plain"><h2 class="nl-card__h" id="nl-actions-title"><span class="nl-card__title">Other actions</span></h2></header>
                <div class="nl-card__body nl-actions">
                    <div class="nl-actions__row">
                        <div><strong>Make a copy</strong><p>Creates a new draft with the same text and pictures.</p></div>
                        <button type="button" class="btn btn--outline btn--sm" data-act="duplicate"><?= icon('copy', 'icon--sm') ?> Duplicate</button>
                    </div>
                    <div class="nl-actions__row" data-archive-row>
                        <div><strong data-archive-title>Archive</strong><p data-archive-text>Keeps the issue but takes it off the website.</p></div>
                        <button type="button" class="btn btn--outline btn--sm" data-act="archive"><?= icon('folder', 'icon--sm') ?> <span data-archive-label>Archive</span></button>
                    </div>
                    <div class="nl-actions__row nl-actions__row--danger">
                        <div><strong>Delete this issue</strong><p data-delete-text>Removes the issue, its history<?= $pdfIssue ? ' and its PDF file' : '' ?> for good. This cannot be undone.</p></div>
                        <button type="button" class="btn btn--danger btn--sm" data-act="delete"><?= icon('trash', 'icon--sm') ?> Delete</button>
                    </div>
                </div>
            </section>
        </div>

        <!-- ================================================================= right: live preview -->
        <aside class="nl-pane nl-pane--preview" id="nl-pane-preview" data-pane="preview" aria-label="Live preview">
            <div class="nl-preview">
                <div class="nl-preview__bar">
                    <span class="nl-preview__label">Live preview</span>
                    <span class="nl-preview__state" data-preview-state>Up to date</span>
                    <div class="nl-seg" role="group" aria-label="Preview width">
                        <button type="button" aria-pressed="true" data-device="desktop"><?= icon('monitor', 'icon--sm') ?> <span>Computer</span></button>
                        <button type="button" aria-pressed="false" data-device="mobile"><?= icon('smartphone', 'icon--sm') ?> <span>Phone</span></button>
                    </div>
                </div>
                <div class="nl-preview__stage" data-stage>
                    <div class="nl-device" data-device-box data-device-type="desktop"></div>
                </div>
                <p class="nl-preview__hint"><?= icon('info', 'icon--sm') ?> Click any part of the preview to edit it. The preview is made by the website itself, so it matches what visitors will see.</p>
            </div>
        </aside>
    </div>

    <!-- ===================================================================== hidden forms (delete and duplicate keep their normal confirmation and message) -->
    <form method="post" action="<?= e(url('admin/newsletters/' . $id . '/delete')) ?>" data-no-guard data-delete-form hidden><?= csrf_field() ?></form>
    <form method="post" action="<?= e(url('admin/newsletters/' . $id . '/duplicate')) ?>" data-no-guard data-duplicate-form hidden><?= csrf_field() ?></form>

    <!-- ===================================================================== dialogs -->
    <dialog class="nl-dialog" data-dialog="confirm" data-backdrop-close aria-labelledby="nl-confirm-title" aria-describedby="nl-confirm-body">
        <form method="dialog" class="nl-dialog__box">
            <h2 class="nl-dialog__title" id="nl-confirm-title" data-confirm-title>Are you sure?</h2>
            <div class="nl-dialog__body" id="nl-confirm-body" data-confirm-body></div>
            <div class="nl-dialog__actions">
                <button type="submit" class="btn btn--ghost" value="cancel" data-confirm-cancel>Cancel</button>
                <button type="submit" class="btn btn--primary" value="ok" data-confirm-ok>OK</button>
            </div>
        </form>
    </dialog>

    <dialog class="nl-dialog" data-dialog="schedule" aria-labelledby="nl-sched-title" aria-describedby="nl-sched-body">
        <form method="dialog" class="nl-dialog__box" data-schedule-form novalidate>
            <h2 class="nl-dialog__title" id="nl-sched-title">Schedule this issue</h2>
            <div class="nl-dialog__body" id="nl-sched-body">
                <p>Choose when the issue should appear on the website. It stays hidden until then, and goes live by itself.</p>
                <div class="field">
                    <label class="field__label" for="nl-sched-at">Date and time</label>
                    <input class="input" type="datetime-local" id="nl-sched-at" step="60" required data-schedule-input aria-describedby="nl-sched-help">
                    <p class="field__help" id="nl-sched-help">Times are Kampala time (East Africa Time, <span data-tz-name>Africa/Kampala</span>). It is now <strong data-schedule-now></strong> there.</p>
                    <p class="field__error" data-schedule-error role="alert" hidden></p>
                </div>
                <p class="nl-dialog__summary" data-schedule-summary aria-live="polite"></p>
            </div>
            <div class="nl-dialog__actions">
                <button type="submit" class="btn btn--ghost" value="cancel" formnovalidate>Cancel</button>
                <button type="submit" class="btn btn--primary" value="ok" data-schedule-ok>Schedule issue</button>
            </div>
        </form>
    </dialog>

    <dialog class="nl-dialog" data-dialog="conflict" aria-labelledby="nl-conflict-title" aria-describedby="nl-conflict-body">
        <form method="dialog" class="nl-dialog__box">
            <h2 class="nl-dialog__title" id="nl-conflict-title">Somebody else changed this issue</h2>
            <div class="nl-dialog__body" id="nl-conflict-body">
                <p data-conflict-text></p>
                <p>What would you like to do?</p>
                <ul class="nl-dialog__choices">
                    <li><strong>Use their version.</strong> Your unsaved changes on this page are dropped.</li>
                    <li><strong>Keep my version.</strong> Your text replaces theirs. Theirs stays in History, so it can be brought back.</li>
                </ul>
            </div>
            <div class="nl-dialog__actions nl-dialog__actions--wrap">
                <button type="submit" class="btn btn--ghost" value="later">Decide later</button>
                <button type="submit" class="btn btn--outline" value="theirs">Use their version</button>
                <button type="submit" class="btn btn--primary" value="mine">Keep my version</button>
            </div>
        </form>
    </dialog>

    <dialog class="nl-dialog" data-dialog="session" data-backdrop-close aria-labelledby="nl-session-title" aria-describedby="nl-session-body">
        <form method="dialog" class="nl-dialog__box">
            <h2 class="nl-dialog__title" id="nl-session-title">Please sign in again</h2>
            <div class="nl-dialog__body" id="nl-session-body">
                <p>Your session has ended, so your latest changes could not be saved. They are still on this page and a safety copy is kept in this browser.</p>
                <p>Open the sign-in page in a new tab, sign in, then come back here and press <strong>Save</strong>.</p>
            </div>
            <div class="nl-dialog__actions">
                <button type="submit" class="btn btn--ghost" value="close">Close</button>
                <a class="btn btn--primary" href="<?= e($urls['login']) ?>" target="_blank" rel="noopener">Open sign-in page</a>
            </div>
        </form>
    </dialog>

    <dialog class="nl-dialog nl-drawer" data-dialog="history" aria-labelledby="nl-history-title">
        <div class="nl-drawer__box">
            <header class="nl-drawer__head">
                <h2 class="nl-dialog__title" id="nl-history-title">History</h2>
                <button type="button" class="nl-tool" data-history-close aria-label="Close history"><?= icon('x') ?></button>
            </header>
            <p class="nl-drawer__intro">The last 30 saved versions of this issue. Choose one to see what is different, then bring it back if you want it.</p>
            <div class="nl-drawer__body" data-history-body aria-live="polite"></div>
        </div>
    </dialog>

    <div class="nl-toasts" data-toasts role="status" aria-live="polite"></div>
    <div class="visually-hidden" data-announce role="status" aria-live="polite"></div>

    <?= partial('admin/newsletters/_templates') ?>
</div>
