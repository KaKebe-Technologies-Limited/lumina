<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `applications`. Generic API: see App\Core\Model. */
final class Application extends Model
{
    protected static string $table = 'applications';
    protected static string $order = 'id DESC';

    public const STATUSES = ['new', 'reviewing', 'shortlisted', 'rejected', 'hired'];
}
