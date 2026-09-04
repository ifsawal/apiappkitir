<?php

use App\Http\Controllers\api\v1\Auth\OauthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/snap/v1.0/access-token/b2b', [OauthController::class,'token']);
Route::post('/snap/v1.0/transfer-va/notify-payment-intrabank', [OauthController::class,'token']);
Route::middleware('webhook.token')->group(function () {
    Route::post('/webhook', [OauthController::class,'notif']);

});