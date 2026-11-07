<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Tests\OpenAIs\DalleControllers;
use App\Http\Controllers\API\Mobile\AuthController;
use App\Http\Controllers\API\Mobile\DashboardController;
use App\Http\Controllers\API\Mobile\PledgeCampaignController;
use App\Http\Controllers\API\Mobile\PledgeController;
use App\Http\Controllers\API\Mobile\ProgramAttendanceController;
use App\Http\Controllers\API\Mobile\ProgramController;
use App\Http\Controllers\API\Mobile\ProgramRegistrationController;
use App\Http\Controllers\API\Programs\ChurchServiceController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Portal Users Auth

Route::middleware(['auth:sanctum'])->group(function ()
{

});


// Mobile app (Flutter) API
Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

    // Reachable while must_change_password is still set.
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('password/change', [AuthController::class, 'changePassword']);
    });

    // menu.access: the same menus as the portal (Security & Settings > Access Control).
    Route::middleware(['auth:sanctum', 'password.changed', 'menu.access'])->name('api.')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Church services: member QR + usher check-in (same logic as the portal page)
        Route::get('me/checkin-qr', [ChurchServiceController::class, 'apiMyQr'])->name('me.checkin-qr');
        Route::get('services/today', [ChurchServiceController::class, 'apiToday'])->name('services.today');
        Route::get('services/occurrences/{occurrence}/members', [ChurchServiceController::class, 'searchMembers'])->name('services.members');
        Route::post('services/occurrences/{occurrence}/members/{member}', [ChurchServiceController::class, 'checkInMember'])->name('services.check-in');
        Route::post('services/occurrences/{occurrence}/scan', [ChurchServiceController::class, 'scanCheckIn'])->name('services.scan');
        Route::post('services/occurrences/{occurrence}/new-souls', [ChurchServiceController::class, 'addNewSouls'])->name('services.new-souls');

        // Follow-up of new souls: status, cell, foundation classes, baptism.
        Route::get('invitees', [\App\Http\Controllers\API\Mobile\InviteeController::class, 'index'])->name('invitees.index');
        Route::get('invitees/{invitee}', [\App\Http\Controllers\API\Mobile\InviteeController::class, 'show'])->name('invitees.show');
        Route::post('invitees/{invitee}/follow-up', [\App\Http\Controllers\API\Mobile\InviteeController::class, 'update'])->name('invitees.update');
        Route::post('invitees/{invitee}/make-member', [\App\Http\Controllers\API\Mobile\InviteeController::class, 'makeMember'])->name('invitees.make-member');

        // My extra phone numbers.
        Route::get('me/phones', [\App\Http\Controllers\API\Mobile\MyPhonesController::class, 'index'])->name('me.phones.index');
        Route::post('me/phones', [\App\Http\Controllers\API\Mobile\MyPhonesController::class, 'store'])->name('me.phones.store');
        Route::delete('me/phones/{phone}', [\App\Http\Controllers\API\Mobile\MyPhonesController::class, 'destroy'])->name('me.phones.destroy');

        Route::get('pledges/campaigns', [PledgeCampaignController::class, 'index'])->name('pledges.campaigns');
        Route::get('pledges/members/search', [PledgeController::class, 'searchMembers'])->name('pledges.members.search');
        Route::post('pledges', [PledgeController::class, 'store'])->name('pledges.store');
        Route::get('pledges', [PledgeController::class, 'index'])->name('pledges.index');
        Route::get('pledges/{pledge}', [PledgeController::class, 'show'])->name('pledges.show');
        Route::get('pledges/{pledge}/pdf', [PledgeController::class, 'downloadPdf'])->name('pledges.pdf');

        Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');
        // Literal routes registered before the {program}/{registration}
        // wildcard routes below, same ordering caution routes/web.php calls
        // out for its programs/browse vs programs/{program}.
        Route::get('programs/churches', [ProgramRegistrationController::class, 'churches'])->name('programs.churches');
        Route::get('programs/members/search', [ProgramRegistrationController::class, 'searchMembers'])->name('programs.members.search');
        Route::get('programs/registrations', [ProgramRegistrationController::class, 'index'])->name('programs.registrations.index');
        Route::get('programs/registrations/{registration}', [ProgramRegistrationController::class, 'show'])->name('programs.registrations.show');
        Route::get('programs/registrations/{registration}/pdf', [ProgramRegistrationController::class, 'downloadPdf'])->name('programs.registrations.pdf');
        Route::post('programs/registrations/{registration}/payments', [ProgramRegistrationController::class, 'submitPayment'])->name('programs.registrations.payments');
        Route::get('programs/payment-methods', [ProgramRegistrationController::class, 'paymentMethods'])->name('programs.payment-methods');
        Route::post('programs/{program}/register', [ProgramRegistrationController::class, 'store'])->name('programs.register');

        Route::get('programs/scan/{registration}', [ProgramAttendanceController::class, 'scan'])->name('programs.scan.show');
        Route::post('programs/scan/{registration}/check-in', [ProgramAttendanceController::class, 'checkIn'])->name('programs.scan.check-in');
    });
});


Route::group(['prefix' => 'v1/','middleware' => ['auth:sanctum']], function()
{


});



