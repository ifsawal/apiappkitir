<?php

use App\Http\Controllers\api\v1\Auth\AuthController;
use App\Http\Controllers\api\v1\Kitir\KitirController;
use App\Http\Controllers\api\v1\Kitir\Penjualan;
use App\Http\Controllers\api\v1\Kitir\PenjualanController;
use App\Http\Controllers\api\v1\Pangkalan\KController;
use App\Http\Controllers\api\v1\TestController;
use App\Models\Kitir;
use App\Models\KitirPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function() {
    return response()->json(['status' => 'OK']);
});

Route::prefix('v1')->group(function () {


    // Route::get('/tes', 'TestController@tes');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login-pangkalan', [AuthController::class, 'login_pangkalan']);


    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/tes', [TestController::class, 'tes']);


        Route::get('/kitir/{tanggal}', [KitirController::class, 'kitir']);
        Route::post('/jual', [KitirController::class, 'jual']);
        Route::post('/jual-tambah', [KitirController::class, 'jual_tambah']);
        Route::get('/penjualan', [PenjualanController::class, 'penjualan']);
    });

    Route::middleware(['auth:sanctum', 'abilities:pangkalan2'])->group(function () {
        Route::get('/logout-pangkalan', [AuthController::class, 'logout_pangkalan']);
        Route::get('/k', [KController::class, 'ktp']);
        Route::post('/k', [KController::class, 'simpan_k']);

    });
});
