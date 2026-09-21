<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Tests\OpenAIs\DalleControllers;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Portal Users Auth

Route::middleware(['auth:sanctum'])->group(function () 
{
    
});


Route::group(['prefix' => 'v1/','middleware' => ['auth:sanctum']], function()
{
   
   
});



