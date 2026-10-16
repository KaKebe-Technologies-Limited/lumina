<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `audit_log`. Generic API: see App\Core\Model. */
final class AuditEntry extends Model
{
    protected static string $table = 'audit_log';
    protected static string $order = 'id DESC';
}
