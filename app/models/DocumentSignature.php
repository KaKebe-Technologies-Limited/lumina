<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/** Table `document_signatures` (a signatory attached to a document with a role; signed_at NULL = awaiting). Generic API: see App\Core\Model. */
final class DocumentSignature extends Model
{
    protected static string $table = 'document_signatures';
    protected static string $order = 'sort_order ASC, id ASC';

    /** Roles offered when attaching a signatory (free text is allowed too). */
    public const ROLES = ['Approved by', 'Endorsed by', 'Reviewed by', 'Prepared by', 'Witnessed by'];
}
