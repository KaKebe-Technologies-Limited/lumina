<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\AdminController;
use App\Core\Request;
use App\Core\Response;
use App\Services\Privacy\DataProtectionStatus;
use App\Services\Privacy\DataRights;
use App\Services\Privacy\Notices;

/**
 * Admin > Data protection (settings.manage): inventory summary, data-subject requests, retention reminders and the owner checklist.
 * Route: GET admin/data-protection. Read-only; requests are answered from Admin > Messages.
 */
final class DataProtectionController extends AdminController
{
    public function index(Request $request): Response
    {
        $this->authorize('settings.manage');
        return $this->render('admin/data-protection/index', [
            'counts'    => DataRights::counts(),
            'requests'  => DataRights::recent(30),
            'types'     => DataRights::TYPES,
            'retention' => DataProtectionStatus::retention(),
            'checklist' => DataProtectionStatus::checklist(),
            'activities' => Notices::ITEMS,
            'days'      => DataRights::targetDays(),
        ], ['title' => 'Data protection', 'nav' => 'dataprot', 'breadcrumbs' => [['Data protection', null]]]);
    }
}
