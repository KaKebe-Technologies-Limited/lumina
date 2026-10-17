<?php
/** Divider block (a short gold rule). Vars: $block, $issue, $context. OWNER: WEB agent. */
$preview = $context === 'preview';
?>
<hr class="nl-block nl-block--divider"<?= $preview ? ' data-nl-block="' . e($block['id']) . '" tabindex="0"' : '' ?>>
