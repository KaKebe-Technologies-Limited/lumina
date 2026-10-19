<?php
/**
 * One answer (the <dd> content) of a stored submission, rendered from the SNAPSHOT entry. Used by show.php and print.php.
 * Vars: $e (snapshot entry: id key type label page value text [other] [at]), $files (form_files rows of this field), $base (admin path of the inbox),
 * $sid, $print (bool: no links, no downloads).
 * Everything printed is escaped. Links are made ONLY for a value that is entirely one http(s) address, an e-mail field, or a phone field; they carry
 * rel="noopener noreferrer nofollow" because the text came from a visitor.
 */
$type = (string) ($e['type'] ?? 'text');
$text = trim((string) ($e['text'] ?? ''));
$print = !empty($print);
$isUrl = static fn(string $s): bool => $s !== '' && strlen($s) <= 500 && preg_match('~^https?://[^\s<>"\']+$~i', $s) === 1 && filter_var($s, FILTER_VALIDATE_URL) !== false;
?>
<?php if ($type === 'file'): ?>
    <?php if ($files === []): ?>
        <span class="text-muted">No file<?= $text !== '' ? ' (' . e($text) . ' was removed)' : '' ?></span>
    <?php else: ?>
        <ul class="fs-files">
            <?php foreach ($files as $file): ?>
                <li>
                    <?php if ($print): ?>
                        <?= e($file['original_name']) ?> <span class="small text-muted">(<?= e(human_filesize((int) $file['size'])) ?>)</span>
                    <?php else: ?>
                        <a class="fs-file" href="<?= e(url($base . '/' . (int) $sid . '/files/' . (int) $file['id'])) ?>" download><?= icon('download', 'icon--sm') ?> <span class="fs-file__name"><?= e($file['original_name']) ?></span></a>
                        <span class="small text-muted">(<?= e(strtoupper(pathinfo((string) $file['stored_path'], PATHINFO_EXTENSION))) ?>, <?= e(human_filesize((int) $file['size'])) ?>)</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php elseif ($text === ''): ?>
    <span class="text-muted">No answer</span>
<?php elseif ($type === 'email' && !$print && \App\Services\Forms\FormValidator::plainEmail($text)): ?>
    <a href="mailto:<?= e(rawurlencode($text)) ?>"><?= e($text) ?></a>
<?php elseif ($type === 'phone' && !$print && preg_match('/^\+?[0-9]{6,16}$/', $text) === 1): ?>
    <a href="tel:<?= e($text) ?>"><?= e($text) ?></a>
<?php elseif (in_array($type, ['text'], true) && !$print && $isUrl($text)): ?>
    <a href="<?= e($text) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($text) ?> <?= icon('external-link', 'icon--sm') ?></a>
<?php elseif ($type === 'rating'): ?>
    <span class="fs-stars" aria-hidden="true"><?= str_repeat('&#9733;', (int) ($e['value'] ?? 0)) ?><span class="fs-stars__off"><?= str_repeat('&#9733;', max(0, 5 - (int) ($e['value'] ?? 0))) ?></span></span>
    <span><?= e($text) ?></span>
<?php elseif ($type === 'date'): ?>
    <?= e(preg_match('/^\d{4}-\d{2}-\d{2}$/', $text) === 1 ? format_date($text, 'j F Y') : $text) ?>
<?php elseif ($type === 'consent'): ?>
    <span><?= icon('check-circle', 'icon--sm') ?> <?= e($text) ?></span><?php if (!empty($e['at'])): ?> <span class="small text-muted">(<?= e(format_datetime((string) $e['at'])) ?>)</span><?php endif; ?>
<?php else: ?>
    <span class="fs-text"><?= e($text) ?></span>
<?php endif; ?>
