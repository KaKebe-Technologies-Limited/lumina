<?php
/**
 * Search-engine panel (card): seo_title + seo_description. OWNER: B4.
 * Vars: $row (current values), $titleHint (what is used when the SEO title is empty, e.g. 'the title').
 */
$titleHint = $titleHint ?? 'the title';
?>
<section class="card" aria-labelledby="seo-card-title">
    <div class="card__header"><h2 class="card__title" id="seo-card-title">Search engines (SEO)</h2></div>
    <div class="card__body">
        <?= partial('partials/form-field', [
            'name' => 'seo_title', 'label' => 'SEO title', 'value' => old('seo_title', $row['seo_title'] ?? ''), 'maxlength' => 190,
            'help' => 'Shown in Google results and browser tabs. Leave empty to use ' . $titleHint . '.',
            'attrs' => ['data-maxlength-counter' => true],
        ]) ?>
        <?= partial('partials/form-field', [
            'name' => 'seo_description', 'label' => 'SEO description', 'type' => 'textarea', 'rows' => 3, 'maxlength' => 175,
            'value' => old('seo_description', $row['seo_description'] ?? ''),
            'help' => 'One or two sentences (about 160 characters; anything over 175 is cut) shown under the title in search results and when the page is shared.',
            'attrs' => ['data-maxlength-counter' => true],
        ]) ?>
    </div>
</section>
