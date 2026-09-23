<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Tests\OpenAIs\DalleControllers;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\DashboardController;
use App\Http\Controllers\Api\Mobile\PledgeCampaignController;
use App\Http\Controllers\Api\Mobile\PledgeController;
use App\Http\Controllers\Api\Mobile\ProgramAttendanceController;
use App\Http\Controllers\Api\Mobile\ProgramController;
use App\Http\Controllers\Api\Mobile\ProgramRegistrationController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Portal Users Auth

Route::middleware(['auth:sanctum'])->group(function ()
{

});


// Mobile app (Flutter) API
Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::get('pledges/campaigns', [PledgeCampaignController::class, 'index']);
        Route::get('pledges/members/search', [PledgeController::class, 'searchMembers']);
        Route::post('pledges', [PledgeController::class, 'store']);
        Route::get('pledges', [PledgeController::class, 'index']);
        Route::get('pledges/{pledge}', [PledgeController::class, 'show']);
        Route::get('pledges/{pledge}/pdf', [PledgeController::class, 'downloadPdf']);

        Route::get('programs', [ProgramController::class, 'index']);
        // Literal routes registered before the {program}/{registration}
        // wildcard routes below, same ordering caution routes/web.php calls
        // out for its programs/browse vs programs/{program}.
        Route::get('programs/members/search', [ProgramRegistrationController::class, 'searchMembers']);
        Route::get('programs/registrations', [ProgramRegistrationController::class, 'index']);
        Route::get('programs/registrations/{registration}', [ProgramRegistrationController::class, 'show']);
        Route::get('programs/registrations/{registration}/pdf', [ProgramRegistrationController::class, 'downloadPdf']);
        Route::post('programs/{program}/register', [ProgramRegistrationController::class, 'store']);

        Route::get('programs/scan/{registration}', [ProgramAttendanceController::class, 'scan']);
        Route::post('programs/scan/{registration}/check-in', [ProgramAttendanceController::class, 'checkIn']);
    });
});


Route::group(['prefix' => 'v1/','middleware' => ['auth:sanctum']], function()
{


});



