<?php
/** Maintenance page (HTTP 503, APP_MAINTENANCE=1). Signed-in staff and /admin keep working. Vars: $status, $title, $message. */
?>
<?= partial('web/errors/_panel', ['status' => $status, 'title' => $title, 'message' => $message, 'debug' => null, 'maintenance' => true]) ?>
