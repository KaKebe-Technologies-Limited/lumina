<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `document_signatories` (people responsible for policies, with their signature image). Generic API: see App\Core\Model. */
final class DocumentSignatory extends Model
{
    protected static string $table = 'document_signatories';
    protected static string $order = 'name ASC, id ASC';
}
