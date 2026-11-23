<?php
/** Consent tick box for the newsletter forms (consent time = subscribers.subscribed_at). Vars: $id. */
use App\Services\Privacy\Notices;

$id = (string) ($id ?? 'nl-consent');
?>
<input type="hidden" name="consent_notice" value="1">
<label class="nl-consent" for="<?= e($id) ?>">
    <input type="checkbox" id="<?= e($id) ?>" name="consent" value="1" required>
    <span><?= e((string) content('privacy', 'notes.newsletter_tick', 'I agree to receive the newsletter by e-mail. I can unsubscribe at any time.')) ?> <a href="<?= e(Notices::url('newsletter')) ?>"><?= e((string) content('privacy', 'notes.newsletter_link', 'Why we ask')) ?></a></span>
</label>
