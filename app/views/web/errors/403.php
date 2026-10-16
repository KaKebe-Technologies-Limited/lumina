<?php
/** Themed 403 (access denied / expired form tokens are shown by 419 -> error.php). Vars: $status, $title, $message, $debug. */
$this->start('scripts'); ?>
<script src="<?= e(asset('js/pages/legal.js')) ?>" defer></script>
<?php $this->end(); ?>
<?= partial('web/errors/_panel', ['status' => $status, 'title' => $title, 'message' => $message, 'debug' => $debug ?? null]) ?>
