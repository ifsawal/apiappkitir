<?php

use App\Http\Controllers\api\v1\Auth\AuthController;
use App\Http\Controllers\api\v1\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {


    // Route::get('/tes', 'TestController@tes');
    
    Route::post('/login', [AuthController::class, 'login']);
    
    
    Route::middleware(['auth:sanctum'])->group(function(){
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/tes', [TestController::class, 'tes']);


    });
});
