<?php
/**
 * "Technical details" card. Vars: $row (needs created_at, updated_at, ip?, user_agent?), $title.
 */
use App\Services\Inbox\Support;
?>
<section class="card">
    <div class="card__header"><h2 class="card__title"><?= e($title ?? 'Details') ?></h2></div>
    <div class="card__body">
        <dl class="definition-list inbox-dl">
            <dt>Received</dt><dd><?= e(format_datetime($row['created_at'])) ?></dd>
            <?php if (!empty($row['updated_at']) && $row['updated_at'] !== $row['created_at']): ?>
                <dt>Last updated</dt><dd><?= e(format_datetime($row['updated_at'])) ?></dd>
            <?php endif; ?>
            <?php if (!empty($row['ip'])): ?><dt>IP address</dt><dd><?= e($row['ip']) ?></dd><?php endif; ?>
            <?php if (!empty($row['user_agent'])): ?><dt>Browser</dt><dd title="<?= e($row['user_agent']) ?>"><?= e(Support::agent((string) $row['user_agent'])) ?></dd><?php endif; ?>
        </dl>
    </div>
</section>
