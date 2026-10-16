<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `contact_messages`. Generic API: see App\Core\Model. */
final class ContactMessage extends Model
{
    protected static string $table = 'contact_messages';
    protected static string $order = 'id DESC';

    public const STATUSES = ['new', 'read', 'replied', 'archived'];
}
