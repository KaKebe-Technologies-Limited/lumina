<?php
/** Themed 500 (something went wrong). Vars: $status, $title, $message, $debug (only with APP_DEBUG=1). */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/legal.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('web/errors/_panel', ['status' => $status, 'title' => $title, 'message' => $message, 'debug' => $debug ?? null]) ?>
