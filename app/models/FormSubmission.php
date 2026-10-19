<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Table `form_submissions` = one answer set sent through a form. PERSONAL DATA: only read from admin screens guarded by forms.submissions.
 * Write through App\Services\Forms\SubmissionService (it keeps forms.submissions_count, the snapshot and the search text in step).
 */
final class FormSubmission extends Model
{
    protected static string $table = 'form_submissions';
    protected static string $order = 'created_at DESC, id DESC';

    /** Every status a submission can have. `new` is set on creation; the others are chosen by staff. */
    public const STATUSES = ['new', 'read', 'starred', 'shortlisted', 'rejected', 'accepted', 'archived', 'spam'];
}
