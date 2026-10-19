<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `forms` = a form created in Admin > Forms (docs/FORMS.md). Rows are raw (definition / settings are JSON strings): use
 * App\Services\Forms\FormService::hydrate() to get the decoded definition and settings, and FormService for every write.
 */
final class Form extends Model
{
    protected static string $table = 'forms';
    protected static string $order = 'updated_at DESC, id DESC';

    public const STATUSES = ['draft', 'published', 'closed', 'archived'];
}
