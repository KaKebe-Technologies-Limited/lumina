<?php
/**
 * Bono AI chat window (docs/BONO.md), on every public page. Everything starts closed; assets/js/bono.js drives it. Vars: none.
 */
use App\Services\Bono\BonoEngine;

if (!BonoEngine::enabled()) {
    return;
}
$sw = \App\Services\Academy\LearnerAuth::supportWhatsapp();
?>
<div class="bono" data-bono data-ask="<?= e(url('bono/ask')) ?>" data-feedback="<?= e(url('bono/feedback')) ?>" data-greeting="<?= e(BonoEngine::greeting()) ?>" data-suggest="<?= e((string) json_encode(BonoEngine::suggestions(), JSON_UNESCAPED_UNICODE)) ?>">
    <button class="bono__launch" type="button" data-bono-open aria-haspopup="dialog" aria-expanded="false" aria-controls="bono-panel"><?= icon('message-circle', 'icon--md') ?><span>Ask Bono AI</span></button>
    <section class="bono__panel" id="bono-panel" role="dialog" aria-label="Bono AI, the support assistant" hidden>
        <header class="bono__head">
            <span class="bono__avatar" aria-hidden="true">B</span>
            <div class="bono__title"><strong>Bono AI</strong><small>Support assistant</small></div>
            <button class="bono__close" type="button" data-bono-close aria-label="Close the chat"><?= icon('x', 'icon--sm') ?></button>
        </header>
        <div class="bono__log" data-bono-log role="log" aria-live="polite" tabindex="0"></div>
        <form class="bono__form" data-bono-form novalidate>
            <label class="visually-hidden" for="bono-q">Your question</label>
            <input class="bono__input" id="bono-q" name="q" type="text" maxlength="500" autocomplete="off" placeholder="Ask me about the foundation...">
            <button class="bono__send" type="submit" aria-label="Send"><?= icon('send', 'icon--sm') ?></button>
        </form>
        <p class="bono__note">Bono AI answers from this website and learns it every day.<?php if ($sw !== null): ?> For anything urgent: <a href="<?= e($sw['url']) ?>" target="_blank" rel="noopener">WhatsApp <?= e($sw['display']) ?></a>.<?php endif; ?></p>
    </section>
</div>
