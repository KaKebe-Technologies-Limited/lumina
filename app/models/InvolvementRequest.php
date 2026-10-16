<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `involvement_requests`. Generic API: see App\Core\Model. */
final class InvolvementRequest extends Model
{
    protected static string $table = 'involvement_requests';
    protected static string $order = 'id DESC';

    public const STATUSES = ['new', 'contacted', 'in_progress', 'closed'];
}
