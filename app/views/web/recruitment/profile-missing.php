<?php /** The profile link is wrong or old. */ ?>
<?= partial('partials/page-hero', ['title' => 'Profile not found', 'eyebrow' => 'Ambassadors', 'lead' => 'This link does not match an ambassador profile.']) ?>
<section class="section ac-auth">
    <div class="container container--narrow"><div class="card"><div class="card__body">
        <p>Check that you copied the whole link from your e-mail. If you cannot find it, <a href="<?= e(url('ambassador/profile')) ?>">ask for it to be sent again</a>.</p>
    </div></div></div>
</section>
