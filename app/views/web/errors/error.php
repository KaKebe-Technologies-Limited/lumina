<?php
/** Generic themed error page (405, 419 "page expired", 429 and anything without its own view). Vars: $status, $title, $message, $debug. */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/legal.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('web/errors/_panel', ['status' => $status, 'title' => $title, 'message' => $message, 'debug' => $debug ?? null]) ?>
