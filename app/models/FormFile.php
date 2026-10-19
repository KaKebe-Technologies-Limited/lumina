<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `form_files` = a file uploaded through a form (stored in storage/private/forms; streamed by an authenticated admin controller only). */
final class FormFile extends Model
{
    protected static string $table = 'form_files';
    protected static string $order = 'id ASC';
}
