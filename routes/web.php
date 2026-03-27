<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Resident\CertificateController as ResidentCertificateController;
use App\Http\Controllers\Resident\IssueController as ResidentIssueController;
use App\Http\Controllers\Resident\AnnouncementController as ResidentAnnouncementController;
use App\Http\Controllers\Resident\PermitController as ResidentPermitController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\IssueController as AdminIssueController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PendingRegistrationsController;
use App\Http\Controllers\Admin\ApprovalHistoryController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\PurokController;
use App\Http\Controllers\Admin\PermitController as AdminPermitController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\HouseholdHeadTransferRequestController;
use App\Http\Controllers\Admin\BlotterController;
use App\Http\Controllers\Admin\SummonController;
use App\Http\Controllers\Admin\HearingController;
use App\Http\Controllers\Admin\BlotterRequestController as AdminBlotterRequestController;
use App\Http\Controllers\Admin\AnnouncementLabelController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\LoginActivityController;
use App\Http\Controllers\Admin\OfficialController;
use App\Http\Controllers\Admin\SmsManagementController;
use App\Http\Controllers\Resident\BlotterRequestController as ResidentBlotterRequestController;
use App\Http\Controllers\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Resident\NotificationController as ResidentNotificationController;
use App\Http\Controllers\Resident\OfficialController as ResidentOfficialController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\BlotterController as StaffBlotterController;
use App\Http\Controllers\Staff\SummonController as StaffSummonController;
use App\Http\Controllers\Staff\HearingController as StaffHearingController;
use App\Http\Controllers\Staff\AnnouncementController as StaffAnnouncementController;
use App\Http\Controllers\Staff\ComplaintController as StaffComplaintController;
use App\Http\Controllers\Staff\RegistrationController as StaffRegistrationController;
use App\Http\Controllers\Staff\ReportController as StaffReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FamilyMemberController;

Route::get('/', [HomeController::class, 'index']);

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Profile & password (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile/password', [AuthController::class, 'showChangePasswordForm'])->name('password.edit');
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/verifications/{type}', [ProfileController::class, 'updateVerification'])->name('profile.verifications.update');
    Route::post('/profile/head-transfer-requests', [ProfileController::class, 'submitHeadTransferRequest'])->name('profile.family-transfer-requests.store');
    // Family member management (head of family only)
    Route::post('/profile/family', [FamilyMemberController::class, 'store'])->name('family.store');
    Route::put('/profile/family/{member}', [FamilyMemberController::class, 'update'])->name('family.update');
    Route::delete('/profile/family/{member}', [FamilyMemberController::class, 'destroy'])->name('family.destroy');
    Route::post('/profile/family/{member}/restore', [FamilyMemberController::class, 'restore'])->name('family.restore');

    // JSON API: resident name search (used by blotter autocomplete)
    Route::get('/api/residents/search', [App\Http\Controllers\Api\ResidentSearchController::class, 'search'])->name('api.residents.search');
});

// About (auth only; resident navbar links here)
Route::middleware('auth')->get('/about', function () {
    $communityStats = [
        'total_people' => \App\Models\User::countable()
            ->where('status', \App\Models\User::STATUS_APPROVED)
            ->count(),
        'total_households' => \App\Models\User::countable()
            ->where('status', \App\Models\User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->count(),
        'total_puroks' => \App\Models\Purok::active()->count(),
    ];

    $officialCards = \App\Models\Official::query()
        ->with([
            'user:id,first_name,middle_name,last_name,suffix',
            'position:id,name,sort_order',
        ])
        ->currentlyServing()
        ->get()
        ->sortBy(function ($official) {
            return [
                $official->position->sort_order ?? 999,
                $official->position->name ?? '',
                $official->user->last_name ?? '',
                $official->user->first_name ?? '',
            ];
        })
        ->values()
        ->map(function ($official) {
            $fullName = $official->user?->full_name
                ?? trim(implode(' ', array_filter([
                    $official->user?->first_name,
                    $official->user?->middle_name,
                    $official->user?->last_name,
                    $official->user?->suffix,
                ])));

            $nameParts = preg_split('/\s+/', trim((string) $fullName)) ?: [];
            $initials = collect($nameParts)
                ->filter()
                ->take(2)
                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                ->implode('');

            return [
                'name' => $fullName !== '' ? $fullName : 'Barangay Official',
                'role' => $official->position->name ?? 'Barangay Official',
                'initials' => $initials !== '' ? $initials : 'BO',
            ];
        });

    return view('about', compact('communityStats', 'officialCards'));
})->name('about');

// Resident routes (auth only)
Route::middleware('auth')->prefix('resident')->name('resident.')->group(function () {
    Route::get('/dashboard', [ResidentDashboardController::class, 'index'])->name('dashboard');
    // Barangay Officials (informational, read-only — powered by officials table)
    Route::get('/officials/council', [ResidentOfficialController::class, 'council'])->name('officials.council');
    Route::get('/officials/sk', [ResidentOfficialController::class, 'sk'])->name('officials.sk');
    // Barangay Services
    Route::get('/certificates', [ResidentCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/create', [ResidentCertificateController::class, 'create'])->name('certificates.create');
    Route::post('/certificates', [ResidentCertificateController::class, 'store'])->name('certificates.store');
    Route::get('/issues', [ResidentIssueController::class, 'index'])->name('issues.index');
    Route::get('/issues/create', [ResidentIssueController::class, 'create'])->name('issues.create');
    Route::post('/issues', [ResidentIssueController::class, 'store'])->name('issues.store');
    Route::get('/issues/{issue}', [ResidentIssueController::class, 'show'])->name('issues.show');
    // Permits
    Route::get('/permits', [ResidentPermitController::class, 'index'])->name('permits.index');
    Route::get('/permits/create', [ResidentPermitController::class, 'create'])->name('permits.create');
    Route::post('/permits', [ResidentPermitController::class, 'store'])->name('permits.store');
    // Blotter Requests
    Route::get('/blotter-requests', [ResidentBlotterRequestController::class, 'index'])->name('blotter-requests.index');
    Route::get('/blotter-requests/create', [ResidentBlotterRequestController::class, 'create'])->name('blotter-requests.create');
    Route::post('/blotter-requests', [ResidentBlotterRequestController::class, 'store'])->name('blotter-requests.store');
    // Announcements
    Route::get('/announcements', [ResidentAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}', [ResidentAnnouncementController::class, 'show'])->name('announcements.show');
    // Notifications
    Route::get('/notifications', [ResidentNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [ResidentNotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{notification}/read', [ResidentNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [ResidentNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Staff routes (auth + role staff or admin)
Route::middleware(['auth', 'role:staff,admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/no-access', function () {
        return view('staff.no-access');
    })->name('no-access');

    // Registration Management
    Route::get('/pending-registrations', [StaffRegistrationController::class, 'index'])->name('pending-registrations.index');
    Route::post('/pending-registrations/{user}/approve', [StaffRegistrationController::class, 'approve'])->name('pending-registrations.approve');
    Route::post('/pending-registrations/{user}/reject', [StaffRegistrationController::class, 'reject'])->name('pending-registrations.reject');
    Route::post('/pending-registrations/{user}/suspend', [StaffRegistrationController::class, 'suspend'])->name('pending-registrations.suspend');
    Route::post('/pending-registrations/{user}/unsuspend', [StaffRegistrationController::class, 'unsuspend'])->name('pending-registrations.unsuspend');
    Route::post('/pending-registrations/bulk-approve', [StaffRegistrationController::class, 'bulkApprove'])->name('pending-registrations.bulk-approve');
    Route::post('/pending-registrations/bulk-reject', [StaffRegistrationController::class, 'bulkReject'])->name('pending-registrations.bulk-reject');
    Route::post('/pending-registrations/send-reminder-now', [StaffRegistrationController::class, 'sendReminderNow'])->name('pending-registrations.send-reminder-now');
    Route::post('/pending-registrations/retry-failed-reminder-jobs', [StaffRegistrationController::class, 'retryFailedReminderJobs'])->name('pending-registrations.retry-failed-reminder-jobs');
    Route::get('/approval-history', [StaffRegistrationController::class, 'approvalHistory'])->name('approval-history.index');
    Route::get('/verifications', [StaffRegistrationController::class, 'verifications'])->name('verifications.index');
    Route::post('/verifications/{user}/pwd/approve', [VerificationController::class, 'approvePwd'])->name('verifications.pwd.approve');
    Route::post('/verifications/{user}/pwd/reject', [VerificationController::class, 'rejectPwd'])->name('verifications.pwd.reject');
    Route::post('/verifications/{user}/senior/approve', [VerificationController::class, 'approveSenior'])->name('verifications.senior.approve');
    Route::post('/verifications/{user}/senior/reject', [VerificationController::class, 'rejectSenior'])->name('verifications.senior.reject');

    // e-Blotter
    Route::get('/blotters', [StaffBlotterController::class, 'index'])->name('blotters.index');
    Route::get('/blotters/create', [StaffBlotterController::class, 'create'])->name('blotters.create');
    Route::post('/blotters', [StaffBlotterController::class, 'store'])->name('blotters.store');
    Route::get('/blotters/{blotter}/edit', [StaffBlotterController::class, 'edit'])->name('blotters.edit');
    Route::put('/blotters/{blotter}', [StaffBlotterController::class, 'update'])->name('blotters.update');
    Route::get('/blotters/{blotter}/download', [StaffBlotterController::class, 'download'])->name('blotters.download');
    Route::get('/blotters/{blotter}/evidence/{type}', [StaffBlotterController::class, 'previewEvidence'])->name('blotters.evidence.preview');
    Route::delete('/blotters/{blotter}', [BlotterController::class, 'archive'])->name('blotters.archive');
    Route::post('/blotters/{id}/restore', [BlotterController::class, 'restore'])->name('blotters.restore');
    Route::get('/blotters/{blotter}/summons', [StaffSummonController::class, 'index'])->name('blotters.summons.index');
    Route::post('/blotters/{blotter}/summons', [StaffSummonController::class, 'store'])->name('blotters.summons.store');
    Route::put('/blotters/{blotter}/summons/{summon}/status', [StaffSummonController::class, 'updateStatus'])->name('blotters.summons.status');
    Route::get('/blotters/{blotter}/summons/{summon}/print', [StaffSummonController::class, 'print'])->name('blotters.summons.print');
    Route::get('/blotters/{blotter}/certification-to-file-action/print', [StaffSummonController::class, 'certificationToFileAction'])->name('blotters.certification.print');
    Route::get('/blotters/{blotter}/hearings', [StaffHearingController::class, 'index'])->name('blotters.hearings.index');
    Route::post('/blotters/{blotter}/hearings', [StaffHearingController::class, 'store'])->name('blotters.hearings.store');
    Route::put('/blotters/{blotter}/hearings/{hearing}/start', [StaffHearingController::class, 'start'])->name('blotters.hearings.start');
    Route::put('/blotters/{blotter}/hearings/{hearing}/no-show', [StaffHearingController::class, 'markNoShow'])->name('blotters.hearings.no-show');
    Route::put('/blotters/{blotter}/hearings/{hearing}/complete', [StaffHearingController::class, 'complete'])->name('blotters.hearings.complete');
    Route::put('/blotters/{blotter}/hearings/{hearing}/reschedule', [StaffHearingController::class, 'reschedule'])->name('blotters.hearings.reschedule');
    Route::put('/blotters/{blotter}/hearings/{hearing}/notes', [StaffHearingController::class, 'addNotes'])->name('blotters.hearings.notes');
    Route::get('/blotter-requests', [StaffBlotterController::class, 'requests'])->name('blotter-requests.index');
    Route::put('/blotter-requests/{blotterRequest}', [StaffBlotterController::class, 'updateRequestStatus'])->name('blotter-requests.update');

    // Announcements
    Route::get('/announcements', [StaffAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [StaffAnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [StaffAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [StaffAnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [StaffAnnouncementController::class, 'update'])->name('announcements.update');
    Route::post('/announcements/{announcement}/approve', [AdminAnnouncementController::class, 'approve'])->name('announcements.approve');
    Route::post('/announcements/{announcement}/reject', [AdminAnnouncementController::class, 'reject'])->name('announcements.reject');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::post('/announcements/{id}/restore', [AdminAnnouncementController::class, 'restore'])->name('announcements.restore');

    // Complaints (named issues.* to match shared admin views)
    Route::get('/issues', [StaffComplaintController::class, 'index'])->name('issues.index');
    Route::get('/issues/{complaint}', [StaffComplaintController::class, 'show'])->name('issues.show');
    Route::put('/issues/{complaint}', [StaffComplaintController::class, 'update'])->name('issues.update');
    Route::post('/issues/{complaint}/notes', [StaffComplaintController::class, 'storeNote'])->name('issues.notes.store');
    Route::post('/issues/{complaint}/assign', [StaffComplaintController::class, 'assign'])->name('issues.assign');
    Route::post('/issues/{complaint}/assign-me', [StaffComplaintController::class, 'assignToMe'])->name('issues.assign-me');

    // Reports & Analytics
    Route::get('/reports', [StaffReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/population', [StaffReportController::class, 'population'])->name('reports.population');
    Route::get('/reports/population/export/pdf', [StaffReportController::class, 'populationExportPdf'])->name('reports.population.export.pdf');
    Route::get('/reports/population/export/excel', [StaffReportController::class, 'populationExportExcel'])->name('reports.population.export.excel');
    Route::get('/reports/classification', [StaffReportController::class, 'classification'])->name('reports.classification');
    Route::get('/reports/classification/export/pdf', [StaffReportController::class, 'classificationExportPdf'])->name('reports.classification.export.pdf');
    Route::get('/reports/classification/export/excel', [StaffReportController::class, 'classificationExportExcel'])->name('reports.classification.export.excel');
    Route::get('/reports/services', [StaffReportController::class, 'services'])->name('reports.services');
    Route::get('/reports/services/export/pdf', [StaffReportController::class, 'servicesExportPdf'])->name('reports.services.export.pdf');
    Route::get('/reports/services/export/excel', [StaffReportController::class, 'servicesExportExcel'])->name('reports.services.export.excel');
    Route::get('/reports/households', [StaffReportController::class, 'households'])->name('reports.households');
    Route::get('/reports/households/view', [StaffReportController::class, 'householdsView'])->name('reports.households.view');
    Route::get('/reports/households/view/print', [StaffReportController::class, 'householdsViewPrint'])->name('reports.households.view.print');
    Route::get('/reports/households/view/export/pdf', [StaffReportController::class, 'householdsViewExportPdf'])->name('reports.households.view.export.pdf');
    Route::get('/reports/households/view/export/excel', [StaffReportController::class, 'householdsViewExportExcel'])->name('reports.households.view.export.excel');
    Route::get('/reports/households/head-suggestions', [StaffReportController::class, 'householdHeadSuggestions'])
        ->middleware('throttle:30,1')
        ->name('reports.households.head-suggestions');
    Route::get('/reports/households/timeline', [StaffReportController::class, 'householdsTimeline'])->name('reports.households.timeline');
    Route::get('/reports/households/timeline/export/pdf', [StaffReportController::class, 'householdsTimelineExportPdf'])->name('reports.households.timeline.export.pdf');
    Route::get('/reports/households/export', [StaffReportController::class, 'householdsExport'])->name('reports.households.export');
    Route::get('/reports/households/export/print', [StaffReportController::class, 'householdsExportPrint'])->name('reports.households.export.print');
    Route::get('/reports/households/export/pdf', [StaffReportController::class, 'householdsExportPdf'])->name('reports.households.export.pdf');
    Route::get('/reports/blotter', [StaffReportController::class, 'blotter'])->name('reports.blotter');
    Route::get('/reports/blotter/export/pdf', [StaffReportController::class, 'blotterExportPdf'])->name('reports.blotter.export.pdf');
    Route::get('/reports/blotter/export/excel', [StaffReportController::class, 'blotterExportExcel'])->name('reports.blotter.export.excel');
    Route::get('/reports/export', [StaffReportController::class, 'export'])->name('reports.export');
});

// Pending Registrations + Approval History + Verifications (Admin Panel, staff and admin can access)
Route::middleware(['auth', 'role:staff,admin'])->prefix('admin')->name('admin.')->group(function () {
    // Registration Management (module access enforced)
    Route::middleware('module:registrations')->group(function () {
        Route::get('/pending-registrations', [PendingRegistrationsController::class, 'index'])->name('pending-registrations.index');
        Route::post('/pending-registrations/{user}/approve', [PendingRegistrationsController::class, 'approve'])->name('pending-registrations.approve');
        Route::post('/pending-registrations/{user}/reject', [PendingRegistrationsController::class, 'reject'])->name('pending-registrations.reject');
        Route::post('/pending-registrations/{user}/suspend', [PendingRegistrationsController::class, 'suspend'])->name('pending-registrations.suspend');
        Route::post('/pending-registrations/{user}/unsuspend', [PendingRegistrationsController::class, 'unsuspend'])->name('pending-registrations.unsuspend');
        Route::post('/pending-registrations/bulk-approve', [PendingRegistrationsController::class, 'bulkApprove'])->name('pending-registrations.bulk-approve');
        Route::post('/pending-registrations/bulk-reject', [PendingRegistrationsController::class, 'bulkReject'])->name('pending-registrations.bulk-reject');
        Route::post('/pending-registrations/send-reminder-now', [PendingRegistrationsController::class, 'sendReminderNow'])->name('pending-registrations.send-reminder-now');
        Route::post('/pending-registrations/retry-failed-reminder-jobs', [PendingRegistrationsController::class, 'retryFailedReminderJobs'])->name('pending-registrations.retry-failed-reminder-jobs');
        Route::post('/pending-registrations/settings', [PendingRegistrationsController::class, 'updateEscalationSettings'])->name('pending-registrations.settings.update');
        Route::get('/approval-history', [ApprovalHistoryController::class, 'index'])->name('approval-history.index');
        // Resident Classification Verifications
        Route::get('/verifications', [VerificationController::class, 'index'])->name('verifications.index');
        Route::post('/verifications/{user}/pwd/approve', [VerificationController::class, 'approvePwd'])->name('verifications.pwd.approve');
        Route::post('/verifications/{user}/pwd/reject', [VerificationController::class, 'rejectPwd'])->name('verifications.pwd.reject');
        Route::post('/verifications/{user}/senior/approve', [VerificationController::class, 'approveSenior'])->name('verifications.senior.approve');
        Route::post('/verifications/{user}/senior/reject', [VerificationController::class, 'rejectSenior'])->name('verifications.senior.reject');
        Route::get('/head-transfer-requests', [HouseholdHeadTransferRequestController::class, 'index'])->name('head-transfer-requests.index');
        Route::post('/head-transfer-requests/{transferRequest}/approve', [HouseholdHeadTransferRequestController::class, 'approve'])->name('head-transfer-requests.approve');
        Route::post('/head-transfer-requests/{transferRequest}/reject', [HouseholdHeadTransferRequestController::class, 'reject'])->name('head-transfer-requests.reject');
    });
    // e-Blotter (Internal — Officials only, module access enforced)
    Route::middleware('module:blotter')->group(function () {
        Route::get('/blotters', [BlotterController::class, 'index'])->name('blotters.index');
        Route::get('/blotters/create', [BlotterController::class, 'create'])->name('blotters.create');
        Route::post('/blotters', [BlotterController::class, 'store'])->name('blotters.store');
        Route::get('/blotters/{blotter}/edit', [BlotterController::class, 'edit'])->name('blotters.edit');
        Route::put('/blotters/{blotter}', [BlotterController::class, 'update'])->name('blotters.update');
        Route::get('/blotters/{blotter}/download', [BlotterController::class, 'download'])->name('blotters.download');
        Route::get('/blotters/{blotter}/evidence/{type}', [BlotterController::class, 'previewEvidence'])->name('blotters.evidence.preview');
        Route::delete('/blotters/{blotter}', [BlotterController::class, 'archive'])->name('blotters.archive');
        Route::post('/blotters/{id}/restore', [BlotterController::class, 'restore'])->name('blotters.restore');
        Route::get('/blotters/{blotter}/summons', [SummonController::class, 'index'])->name('blotters.summons.index');
        Route::post('/blotters/{blotter}/summons', [SummonController::class, 'store'])->name('blotters.summons.store');
        Route::put('/blotters/{blotter}/summons/{summon}/status', [SummonController::class, 'updateStatus'])->name('blotters.summons.status');
        Route::get('/blotters/{blotter}/summons/{summon}/print', [SummonController::class, 'print'])->name('blotters.summons.print');
        Route::get('/blotters/{blotter}/certification-to-file-action/print', [SummonController::class, 'certificationToFileAction'])->name('blotters.certification.print');
        Route::get('/blotters/{blotter}/hearings', [HearingController::class, 'index'])->name('blotters.hearings.index');
        Route::post('/blotters/{blotter}/hearings', [HearingController::class, 'store'])->name('blotters.hearings.store');
        Route::put('/blotters/{blotter}/hearings/{hearing}/start', [HearingController::class, 'start'])->name('blotters.hearings.start');
        Route::put('/blotters/{blotter}/hearings/{hearing}/no-show', [HearingController::class, 'markNoShow'])->name('blotters.hearings.no-show');
        Route::put('/blotters/{blotter}/hearings/{hearing}/complete', [HearingController::class, 'complete'])->name('blotters.hearings.complete');
        Route::put('/blotters/{blotter}/hearings/{hearing}/reschedule', [HearingController::class, 'reschedule'])->name('blotters.hearings.reschedule');
        Route::put('/blotters/{blotter}/hearings/{hearing}/notes', [HearingController::class, 'addNotes'])->name('blotters.hearings.notes');
        // Blotter Requests (staff can release, admin can approve/reject — controller enforces)
        Route::get('/blotter-requests', [AdminBlotterRequestController::class, 'index'])->name('blotter-requests.index');
        Route::put('/blotter-requests/{blotterRequest}', [AdminBlotterRequestController::class, 'updateStatus'])->name('blotter-requests.update');
    });
});

// Admin routes (auth + role admin only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $stats = [
            'total_residents' => app(\App\Services\PopulationService::class)->getTotalResidents(),
            'pending_requests' => \App\Models\CertificateRequest::where('status', 'pending')->count(),
            'approved_certificates' => \App\Models\CertificateRequest::where('status', 'approved')->count(),
            'released_certificates' => \App\Models\CertificateRequest::where('status', 'released')->count(),
            'reported_issues' => \App\Models\IssueReport::count(),
            'pending_permits' => \App\Models\Permit::where('status', 'pending')->count(),
        ];

        $recentCertificates = \App\Models\CertificateRequest::with('user')->latest()->take(5)->get();
        $recentIssues = \App\Models\IssueReport::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentCertificates', 'recentIssues'));
    })->name('dashboard');
    Route::get('/residents', [UserController::class, 'index'])->name('residents.index');
    // Duplicate resolver routes are intentionally hidden/disabled for now.
    Route::get('/residents/{user}', [UserController::class, 'show'])->name('residents.show');
    Route::get('/residents/{user}/edit', [UserController::class, 'edit'])->name('residents.edit');
    Route::put('/residents/{user}', [UserController::class, 'update'])->name('residents.update');
    Route::post('/residents/{user}/role', [UserController::class, 'updateRole'])->name('residents.updateRole');
    Route::post('/residents/{user}/suspend', [UserController::class, 'suspend'])->name('residents.suspend');
    Route::post('/residents/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('residents.unsuspend');
    Route::post('/residents/{user}/link-family', [UserController::class, 'linkFamily'])->name('residents.linkFamily');
    Route::post('/residents/{user}/unlink-family', [UserController::class, 'unlinkFamily'])->name('residents.unlinkFamily');
    Route::post('/residents/{user}/transfer-head/{member}', [UserController::class, 'transferHead'])->name('residents.transferHead');
    Route::post('/residents/{user}/permissions', [UserController::class, 'updatePermissions'])->name('residents.updatePermissions');
    Route::post('/residents/{user}/position', [UserController::class, 'updatePosition'])->name('residents.updatePosition');
    // Barangay Services (document requests + issue reports)
    Route::get('/certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{certificate}/review', [AdminCertificateController::class, 'editBeforeApproval'])->name('certificates.review.edit');
    Route::put('/certificates/{certificate}/review', [AdminCertificateController::class, 'updateBeforeApproval'])->name('certificates.review.update');
    Route::post('/certificates/{certificate}', [AdminCertificateController::class, 'update'])->name('certificates.update');
    Route::get('/certificates/{certificate}/residency-template', [AdminCertificateController::class, 'editResidencyTemplate'])->name('certificates.residency-template.edit');
    Route::put('/certificates/{certificate}/residency-template', [AdminCertificateController::class, 'updateResidencyTemplate'])->name('certificates.residency-template.update');
    Route::get('/certificates/{certificate}/residency-template/print', [AdminCertificateController::class, 'printResidencyTemplate'])->name('certificates.residency-template.print');
    Route::get('/certificates/{certificate}/indigency-template/print', [AdminCertificateController::class, 'printIndigencyTemplate'])->name('certificates.indigency-template.print');
    Route::get('/certificates/{certificate}/clearance-template/print', [AdminCertificateController::class, 'printClearanceTemplate'])->name('certificates.clearance-template.print');
    Route::get('/certificates/{certificate}/barangay-certificate-template/print', [AdminCertificateController::class, 'printBarangayCertificateTemplate'])->name('certificates.barangay-certificate-template.print');
    Route::post('/certificates/{certificate}/release', [AdminCertificateController::class, 'release'])->name('certificates.release');
    // Complaints/Issues (module access enforced)
    Route::middleware('module:complaints')->group(function () {
        Route::get('/issues', [AdminIssueController::class, 'index'])->name('issues.index');
        Route::get('/issues/{issue_report}', [AdminIssueController::class, 'show'])->name('issues.show');
        Route::put('/issues/{issue_report}', [AdminIssueController::class, 'update'])->name('issues.update');
        Route::post('/issues/{issue_report}/assign', [AdminIssueController::class, 'assign'])->name('issues.assign');
        Route::post('/issues/{issue_report}/assign-me', [AdminIssueController::class, 'assignToMe'])->name('issues.assign-me');
        Route::post('/issues/{issue_report}/notes', [AdminIssueController::class, 'storeNote'])->name('issues.notes.store');
    });
    // Permits
    Route::get('/permits', [AdminPermitController::class, 'index'])->name('permits.index');
    Route::put('/permits/{permit}/approve', [AdminPermitController::class, 'approve'])->name('permits.approve');
    Route::put('/permits/{permit}/reject', [AdminPermitController::class, 'reject'])->name('permits.reject');
    Route::put('/permits/{permit}/release', [AdminPermitController::class, 'release'])->name('permits.release');
    Route::get('/permits/{permit}/business-template/print', [AdminPermitController::class, 'printBusinessTemplate'])->name('permits.business-template.print');
    Route::get('/permits/{permit}/event-template/print', [AdminPermitController::class, 'printEventTemplate'])->name('permits.event-template.print');
    Route::get('/permits/{permit}/building-template/print', [AdminPermitController::class, 'printBuildingTemplate'])->name('permits.building-template.print');
    Route::get('/permits/{permit}/document', [AdminPermitController::class, 'viewDocument'])->name('permits.document');
    // Announcements (CRUD, module access enforced)
    Route::middleware('module:announcements')->group(function () {
        Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [AdminAnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::post('/announcements/{announcement}/approve', [AdminAnnouncementController::class, 'approve'])->name('announcements.approve');
        Route::post('/announcements/{announcement}/reject', [AdminAnnouncementController::class, 'reject'])->name('announcements.reject');
        Route::post('/announcements/{id}/restore', [AdminAnnouncementController::class, 'restore'])->name('announcements.restore');
        // Announcement Labels
        Route::get('/announcement-labels', [AnnouncementLabelController::class, 'index'])->name('announcement-labels.index');
        Route::post('/announcement-labels', [AnnouncementLabelController::class, 'store'])->name('announcement-labels.store');
        Route::delete('/announcement-labels/{label}', [AnnouncementLabelController::class, 'destroy'])->name('announcement-labels.destroy');
    });
    // Reports & Analytics (module access enforced)
    Route::middleware('module:reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/population', [ReportController::class, 'population'])->name('reports.population');
        Route::get('/reports/population/export/pdf', [ReportController::class, 'populationExportPdf'])->name('reports.population.export.pdf');
        Route::get('/reports/population/export/excel', [ReportController::class, 'populationExportExcel'])->name('reports.population.export.excel');
        Route::get('/reports/classification', [ReportController::class, 'classification'])->name('reports.classification');
        Route::get('/reports/classification/export/pdf', [ReportController::class, 'classificationExportPdf'])->name('reports.classification.export.pdf');
        Route::get('/reports/classification/export/excel', [ReportController::class, 'classificationExportExcel'])->name('reports.classification.export.excel');
        Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services');
        Route::get('/reports/services/export/pdf', [ReportController::class, 'servicesExportPdf'])->name('reports.services.export.pdf');
        Route::get('/reports/services/export/excel', [ReportController::class, 'servicesExportExcel'])->name('reports.services.export.excel');
        Route::get('/reports/households', [ReportController::class, 'households'])->name('reports.households');
        Route::get('/reports/households/view', [ReportController::class, 'householdsView'])->name('reports.households.view');
        Route::get('/reports/households/view/print', [ReportController::class, 'householdsViewPrint'])->name('reports.households.view.print');
        Route::get('/reports/households/view/export/pdf', [ReportController::class, 'householdsViewExportPdf'])->name('reports.households.view.export.pdf');
        Route::get('/reports/households/view/export/excel', [ReportController::class, 'householdsViewExportExcel'])->name('reports.households.view.export.excel');
        Route::get('/reports/households/head-suggestions', [ReportController::class, 'householdHeadSuggestions'])
            ->middleware('throttle:30,1')
            ->name('reports.households.head-suggestions');
        Route::get('/reports/households/timeline', [ReportController::class, 'householdsTimeline'])->name('reports.households.timeline');
        Route::get('/reports/households/timeline/export/pdf', [ReportController::class, 'householdsTimelineExportPdf'])->name('reports.households.timeline.export.pdf');
        Route::get('/reports/households/export', [ReportController::class, 'householdsExport'])->name('reports.households.export');
        Route::get('/reports/households/export/print', [ReportController::class, 'householdsExportPrint'])->name('reports.households.export.print');
        Route::get('/reports/households/export/pdf', [ReportController::class, 'householdsExportPdf'])->name('reports.households.export.pdf');
        Route::get('/reports/blotter', [ReportController::class, 'blotter'])->name('reports.blotter');
        Route::get('/reports/blotter/export/pdf', [ReportController::class, 'blotterExportPdf'])->name('reports.blotter.export.pdf');
        Route::get('/reports/blotter/export/excel', [ReportController::class, 'blotterExportExcel'])->name('reports.blotter.export.excel');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
    // Purok Management
    Route::get('/puroks', [PurokController::class, 'index'])->name('puroks.index');
    Route::get('/puroks/create', [PurokController::class, 'create'])->name('puroks.create');
    Route::post('/puroks', [PurokController::class, 'store'])->name('puroks.store');
    Route::get('/puroks/{purok}/edit', [PurokController::class, 'edit'])->name('puroks.edit');
    Route::put('/puroks/{purok}', [PurokController::class, 'update'])->name('puroks.update');
    Route::post('/puroks/{purok}/toggle-status', [PurokController::class, 'toggleStatus'])->name('puroks.toggle-status');
    // Officials Management
    Route::get('/officials', [OfficialController::class, 'index'])->name('officials.index');
    Route::post('/officials/assign-slot', [OfficialController::class, 'assignSlot'])->name('officials.assign-slot');
    Route::get('/officials/create', [OfficialController::class, 'create'])->name('officials.create');
    Route::post('/officials', [OfficialController::class, 'store'])->name('officials.store');
    Route::get('/officials/{official}/edit', [OfficialController::class, 'edit'])->name('officials.edit');
    Route::put('/officials/{official}', [OfficialController::class, 'update'])->name('officials.update');
    Route::post('/officials/{official}/toggle-active', [OfficialController::class, 'toggleActive'])->name('officials.toggleActive');
    // Audit Log
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    // Login Activity
    Route::get('/login-activities', [LoginActivityController::class, 'index'])->name('login-activities.index');
    // Backups (disabled — re-enable: import BackupController + routes below)
    // Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    // Route::post('/backups/run', [BackupController::class, 'run'])->name('backups.run');
    // Route::post('/backups/run-full', [BackupController::class, 'runFull'])->name('backups.run-full');
    // Route::get('/backups/download/{file}', [BackupController::class, 'download'])->name('backups.download');
    // Route::delete('/backups/{file}', [BackupController::class, 'destroy'])->name('backups.destroy');
    // SMS Management
    Route::get('/sms', [SmsManagementController::class, 'index'])->name('sms.index');
    Route::put('/sms/templates/{template}', [SmsManagementController::class, 'updateTemplate'])->name('sms.templates.update');
    Route::post('/sms/test-send', [SmsManagementController::class, 'sendTest'])->name('sms.test-send');
});

// Legacy redirect: /dashboard -> /resident/dashboard
Route::middleware('auth')->get('/dashboard', function () {
    return redirect()->route('resident.dashboard');
})->name('dashboard');
