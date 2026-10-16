<?php
/** Themed 404. Vars from the ErrorHandler: $status, $title, $message, $debug. Shared markup: _panel.php. */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/legal.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('web/errors/_panel', ['status' => $status, 'title' => $title, 'message' => $message, 'debug' => $debug ?? null, 'suggest' => true]) ?>
