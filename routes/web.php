<?php

use App\Http\Controllers\API\Companies\CompaniesController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\Church\ChurchManagementController;
use App\Http\Controllers\API\Church\MemberManagementController;
use App\Http\Controllers\API\Church\CellManagementController;
use App\Http\Controllers\API\Church\DepartmentManagementController;
use App\Http\Controllers\API\Pledges\PledgeCampaignController;
use App\Http\Controllers\API\Pledges\MyPledgesController;
use App\Http\Controllers\API\Pledges\PledgeManagementController;
use App\Http\Controllers\API\Pledges\PledgeContributionController;
use App\Http\Controllers\API\Pledges\PledgeDashboardController;
use App\Http\Controllers\API\Pledges\PledgeLiveController;
use App\Http\Controllers\API\Pledges\PledgeReportController;
use App\Http\Controllers\API\Pledges\PledgeSettingsController;
use App\Http\Controllers\API\Pledges\PledgeScanController;
use App\Http\Controllers\API\Programs\ProgramController;
use App\Http\Controllers\API\Programs\ProgramAttendanceController;
use App\Http\Controllers\API\Programs\MyProgramRegistrationController;
use App\Http\Controllers\API\Programs\ProgramQrController;
use App\Http\Controllers\API\Programs\NewSoulController;
use App\Http\Controllers\API\Programs\ProgramDashboardController;
use App\Http\Controllers\API\Programs\ProgramReportController;
use App\Http\Controllers\API\Programs\ProgramSettingsController;

use App\Http\Controllers\API\Auth\PortalUsersController;
use Illuminate\Support\Facades\Route;

// app/Http/Controllers/API/Church/ChurchManagementController.php


@include_once('admin_web.php');
// passwordHash


//Portal Users Auth
Route::get('/', [PortalUsersController::class, 'index'])->name('/');
Route::post('/portal/auth', [PortalUsersController::class, 'loginWeb']);

Route::get('/portal/auth', [PortalUsersController::class, 'index'])->name('login');

Route::post('/forget-password', function(){
    return 'Upcoming Soon !';
})->name('forget-password');

Route::get('/how-to-use', [PortalUsersController::class, 'howToUse'])->name('how-to-use');
Route::get('/{id}',[InvoiceController::class, 'download'])->name('free-quotation-download');

Route::group(['prefix' => 'v1/','middleware' => ['auth']], function()
{
    Route::get('logout',[PortalUsersController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('home');
    Route::view('summary', 'admin.dashboard.general_summary')->name('general');
    
    //Security
    // Route::get('church/management', [ChurchManagementController::class, 'get'])->name('church-management');
    Route::get('churches/management', [ChurchManagementController::class, 'index'])->name('churches-management');
    Route::get('churches/export', [ChurchManagementController::class, 'export'])->name('churches-export');
    Route::post('/churches/store', [ChurchManagementController::class, 'store'])->name('churches-store');
    Route::post('/churches/{id}/update', [ChurchManagementController::class, 'update'])->name('churches-update');
    Route::post('/churches/{id}/deactivate', [ChurchManagementController::class, 'deactivate'])->name('churches-deactivate');
    Route::post('/churches/{id}/activate', [ChurchManagementController::class, 'activate'])->name('churches-activate');
    Route::post('churches/transfer', [ChurchManagementController::class, 'transfer'])->name('churches-transfer');
    Route::get('api/churches/by-designation/{designationId}',[ChurchManagementController::class, 'getByDesignation'])->name('churches.by.designation');
    Route::get('churches/tree',[ChurchManagementController::class, 'tree'])->name('churches.tree');
    Route::get('churches/{id}/transfer-history',[ChurchManagementController::class, 'transferHistory'])->name('churches.transfer.history');


    Route::get('member/management', [MemberManagementController::class, 'index'])->name('member.management');
    Route::get('member/export', [MemberManagementController::class, 'export'])->name('members-export');
    Route::get('member/import/template', [MemberManagementController::class, 'importTemplate'])->name('members.import.template');
    Route::post('member/import/preview', [MemberManagementController::class, 'importPreview'])->name('members.import.preview');
    Route::post('member/import', [MemberManagementController::class, 'import'])->name('members.import');
    Route::put('/members/{member}', [MemberManagementController::class, 'update'])->name('members.update');


    Route::get('cell/management', [CellManagementController::class, 'index'])->name('cell.management');
    Route::get('cell/export', [CellManagementController::class, 'export'])->name('cells-export');
    Route::put('cells/{cell}', [CellManagementController::class, 'update'])->name('cells.update');
    Route::delete('cells/{cell}/members/{member}', [CellManagementController::class, 'removeMember'])->name('cells.members.remove');
    // Route::delete('cells/{cell}/members/{member}', [CellManagementController::class, 'removeMember'])->name('cells.members.remove');

    Route::get('department/management', [DepartmentManagementController::class, 'index'])->name('department.management');
    Route::get('department/export', [DepartmentManagementController::class, 'export'])->name('departments-export');
    Route::put('departments/{department}', [DepartmentManagementController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}/members/{member}', [DepartmentManagementController::class, 'removeMember'])->name('departments.members.remove');

    //Pledges - Campaigns
    Route::get('pledges/campaigns', [PledgeCampaignController::class, 'index'])->name('pledge-campaigns.index');
    Route::get('pledges/campaigns/export', [PledgeCampaignController::class, 'export'])->name('pledge-campaigns.export');
    Route::post('pledges/campaigns', [PledgeCampaignController::class, 'store'])->name('pledge-campaigns.store');
    Route::post('pledges/campaigns/{campaign}/update', [PledgeCampaignController::class, 'update'])->name('pledge-campaigns.update');
    Route::post('pledges/campaigns/{campaign}/status/{status}', [PledgeCampaignController::class, 'setStatus'])->name('pledge-campaigns.status');
    Route::get('pledges/campaigns/{campaign}', [PledgeCampaignController::class, 'show'])->name('pledge-campaigns.show');

    //Pledges - My Pledges (member self-service)
    Route::get('pledges/browse', [MyPledgesController::class, 'browse'])->name('my-pledges.browse');
    Route::post('pledges/make', [MyPledgesController::class, 'store'])->name('my-pledges.store');
    Route::get('pledges/my', [MyPledgesController::class, 'index'])->name('my-pledges.index');
    Route::get('pledges/my/{pledge}', [MyPledgesController::class, 'show'])->name('my-pledges.show');
    Route::get('pledges/my/{pledge}/pdf', [MyPledgesController::class, 'downloadPdf'])->name('my-pledges.pdf');

    //Pledges - QR scan landing page
    Route::get('pledges/scan/{pledge}', [PledgeScanController::class, 'show'])->name('pledge-scan.show');

    //Pledges - Staff management / record on behalf
    Route::get('pledges/manage', [PledgeManagementController::class, 'index'])->name('pledge-management.index');
    Route::get('pledges/manage/export', [PledgeManagementController::class, 'export'])->name('pledge-management.export');
    Route::get('pledges/manage/search-members', [PledgeManagementController::class, 'searchMembers'])->name('pledge-management.search-members');
    Route::post('pledges/manage/create-member', [PledgeManagementController::class, 'createMember'])->name('pledge-management.create-member');
    Route::post('pledges/manage/record', [PledgeManagementController::class, 'recordOnBehalf'])->name('pledge-management.record');

    //Pledges - Contributions / Fulfillment
    Route::get('pledges/contributions', [PledgeContributionController::class, 'index'])->name('pledge-contributions.index');
    Route::get('pledges/contributions/export', [PledgeContributionController::class, 'export'])->name('pledge-contributions.export');
    Route::get('pledges/contributions/search-pledges', [PledgeContributionController::class, 'searchPledges'])->name('pledge-contributions.search-pledges');
    Route::post('pledges/contributions', [PledgeContributionController::class, 'store'])->name('pledge-contributions.store');

    //Pledges - Dashboard
    Route::get('pledges/dashboard', [PledgeDashboardController::class, 'index'])->name('pledges.dashboard');

    //Pledges - Live Presentation
    Route::get('pledges/live', [PledgeLiveController::class, 'select'])->name('pledge-live.select');
    Route::get('pledges/live/{campaign}', [PledgeLiveController::class, 'present'])->name('pledge-live.present');
    Route::get('pledges/live/{campaign}/data', [PledgeLiveController::class, 'data'])->name('pledge-live.data');
    Route::post('pledges/live/{campaign}/settings', [PledgeLiveController::class, 'updateSettings'])->name('pledge-live.settings');

    //Pledges - Reports
    Route::get('pledges/reports', [PledgeReportController::class, 'index'])->name('pledge-reports.index');
    Route::get('pledges/reports/staff-recorded', [PledgeReportController::class, 'staffRecorded'])->name('pledge-reports.staff-recorded');
    Route::get('pledges/reports/staff-recorded/export', [PledgeReportController::class, 'exportStaffRecorded'])->name('pledge-reports.staff-recorded.export');

    //Pledges - Settings
    Route::get('pledges/settings', [PledgeSettingsController::class, 'index'])->name('pledge-settings.index');

    //Programs & Attendance - Dashboard
    Route::get('programs-dashboard', [ProgramDashboardController::class, 'index'])->name('programs.dashboard');

    //Programs & Attendance - Programs
    Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('programs/export', [ProgramController::class, 'export'])->name('programs.export');

    //Programs & Attendance - My Registrations (member self-service)
    // NOTE: these single-segment GET routes must be registered before the
    // programs/{program} show route below, otherwise Laravel matches
    // "browse"/"my" as a {program} route-model-binding parameter and 404s.
    Route::get('programs/browse', [MyProgramRegistrationController::class, 'browse'])->name('my-programs.browse');
    Route::get('programs/browse/search-members', [MyProgramRegistrationController::class, 'searchMembers'])->name('my-programs.search-members');
    Route::get('programs/browse/visitor-template', [MyProgramRegistrationController::class, 'visitorTemplate'])->name('my-programs.visitor-template');
    Route::get('programs/my', [MyProgramRegistrationController::class, 'index'])->name('my-programs.index');
    Route::get('programs/my/{registration}', [MyProgramRegistrationController::class, 'show'])->name('my-programs.show');
    Route::get('programs/my/{registration}/pdf', [MyProgramRegistrationController::class, 'downloadPdf'])->name('my-programs.pdf');
    Route::post('programs/{program}/register', [MyProgramRegistrationController::class, 'store'])->name('my-programs.register');
    Route::post('programs/{program}/register/visitors/preview', [MyProgramRegistrationController::class, 'previewVisitors'])->name('my-programs.visitors.preview');
    Route::post('programs/{program}/register/visitors', [MyProgramRegistrationController::class, 'registerVisitors'])->name('my-programs.visitors.register');

    Route::post('programs', [ProgramController::class, 'store'])->name('programs.store');
    Route::post('programs/{program}/update', [ProgramController::class, 'update'])->name('programs.update');
    Route::post('programs/{program}/status/{status}', [ProgramController::class, 'setStatus'])->name('programs.status');
    Route::get('programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

    //Programs & Attendance - Recurring Attendance Capture
    Route::get('programs/{program}/attendance', [ProgramAttendanceController::class, 'capture'])->name('program-attendance.capture');
    Route::post('programs/{program}/attendance', [ProgramAttendanceController::class, 'save'])->name('program-attendance.save');
    Route::post('programs/{program}/attendance/visitor', [ProgramAttendanceController::class, 'addVisitor'])->name('program-attendance.add-visitor');

    //Programs & Attendance - QR scan / check-in landing page
    Route::get('programs/scan/{registration}', [ProgramQrController::class, 'show'])->name('program-scan.show');
    Route::post('programs/scan/{registration}/check-in', [ProgramQrController::class, 'checkIn'])->name('program-scan.check-in');

    //Programs & Attendance - New Souls
    Route::get('new-souls', [NewSoulController::class, 'index'])->name('new-souls.index');
    Route::get('new-souls/dashboard', [NewSoulController::class, 'dashboard'])->name('new-souls.dashboard');
    Route::post('new-souls/{visitor}/status', [NewSoulController::class, 'updateStatus'])->name('new-souls.status');

    //Programs & Attendance - Reports
    Route::get('program-reports', [ProgramReportController::class, 'index'])->name('program-reports.index');
    Route::get('program-reports/registrations', [ProgramReportController::class, 'registrations'])->name('program-reports.registrations');
    Route::get('program-reports/registrations/export', [ProgramReportController::class, 'exportRegistrations'])->name('program-reports.registrations.export');
    Route::get('program-reports/attendance', [ProgramReportController::class, 'attendance'])->name('program-reports.attendance');
    Route::get('program-reports/attendance/export', [ProgramReportController::class, 'exportAttendance'])->name('program-reports.attendance.export');
    Route::get('program-reports/new-souls/export', [ProgramReportController::class, 'exportNewSouls'])->name('program-reports.new-souls.export');
    Route::get('programs/{program}/attendance-pdf', [ProgramReportController::class, 'pdfAttendance'])->name('program-reports.pdf-attendance');

    //Programs & Attendance - Settings
    Route::get('programs-settings', [ProgramSettingsController::class, 'index'])->name('program-settings.index');

    Route::post('pledges/settings/payment-methods', [PledgeSettingsController::class, 'storePaymentMethod'])->name('pledge-settings.payment-methods.store');
    Route::post('pledges/settings/payment-methods/{paymentMethod}/toggle', [PledgeSettingsController::class, 'togglePaymentMethod'])->name('pledge-settings.payment-methods.toggle');
    

    
    



    //Security
    Route::get('security/users', [PortalUsersController::class, 'get'])->name('portal-users');
    Route::post('security/users', [PortalUsersController::class, 'search'])->name('portal-users-search');
    Route::get('security/users/profile', [PortalUsersController::class, 'profile'])->name('security-user-profile');
    Route::post('security/users/profile/change_password', [PortalUsersController::class, 'change_password'])->name('security-user-change_password');
    Route::post('security/users/add', [PortalUsersController::class, 'register'])->name('portal-users-add');
    Route::view('security/configurations', 'admin.security.configurations')->name('security-system-configurations');
    Route::view('security/configurations', 'admin.security.configurations')->name('security-system-configurations');
    Route::view('security/audit', 'admin.security.audit')->name('security-system-audit-trail');
    Route::get('security/users/profile/manage/{id}', [PortalUsersController::class, 'manageProfile'])->name('manage-user-profile');
    Route::get('security/product/profile/manage/{id}/{status}', [PortalUsersController::class, 'updateProdyctStatus'])->name('product-status-update');

       //Properties
    Route::view('properties/reports', 'admin.properties.reports')->name('properties-reports');
    
   
    

});
