<?php

use App\Http\Controllers\api\v1\Akun\DataController;
use App\Http\Controllers\api\v1\Akun\ProsesAkunController;
use App\Http\Controllers\api\v1\Auth\AuthController;
use App\Http\Controllers\api\v1\Auth\OauthController;
use App\Http\Controllers\api\v1\Auth\OnesignalController;
use App\Http\Controllers\api\v1\Kitir\DataPangkalanController;
use App\Http\Controllers\api\v1\Kitir\KitirController;
use App\Http\Controllers\api\v1\Kitir\MapAgenController;
use App\Http\Controllers\api\v1\Kitir\Penjualan;
use App\Http\Controllers\api\v1\Kitir\PenjualanController;
use App\Http\Controllers\api\v1\Kitir\Simelon\SimelonDataController;
use App\Http\Controllers\api\v1\Pangkalan\DasboardController;
use App\Http\Controllers\api\v1\Pangkalan\KController;
use App\Http\Controllers\api\v1\Pangkalan\TransaksiController;
use App\Http\Controllers\api\v1\Pangkalan\TransaksiV2Controller;
use App\Http\Controllers\api\v1\Pembayaran\NotifikasiPembayaranController;
use App\Http\Controllers\api\v1\Pembayaran\PembayaranController;
use App\Http\Controllers\api\v1\Penjualan\PenjualanController as PenjualanPenjualanController;
use App\Http\Controllers\api\v1\Persetujuan\PersetujuanController;
use App\Http\Controllers\api\v1\Quota\DoControleller;
use App\Http\Controllers\api\v1\Quota\QuotaController;
use App\Http\Controllers\api\v1\Tes\NotifController;
use App\Http\Controllers\api\v1\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['status' => 'OK']);
});

Route::prefix('v1')->group(function () {


    // Route::get('/tes', 'TestController@tes');

    Route::post('/login-bersama', [AuthController::class, 'login_bersama']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login-pangkalan', [AuthController::class, 'login_pangkalan']);


    Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::get('/tes', [TestController::class, 'tes']);


        Route::get('/kitir/{tanggal}', [KitirController::class, 'kitir'])->middleware('permission.api:lihat_kitir');
        Route::post('/jual', [KitirController::class, 'jual']);
        Route::post('/jual-tambah', [KitirController::class, 'jual_tambah']);
        Route::get('/penjualan/{tanggal?}', [PenjualanController::class, 'penjualan']);

        Route::get('/simelon-data', [SimelonDataController::class, 'simelon_data']);
        Route::get('/data-pangkalan/{cari?}', [DataPangkalanController::class, 'getPangkalan']);
        Route::get('/map-agen/{id}', [MapAgenController::class, 'script']);

        //akun
        Route::get('/akun', [DataController::class, 'akun']);
        Route::get('/role', [DataController::class, 'role']);
        Route::get('/permisi', [DataController::class, 'permisi']);
        Route::get('/data_user_aktif', [DataController::class, 'data_user_aktif']);
        Route::post('/keluarkan-akun', [ProsesAkunController::class, 'keluarkan_akun']);

        //Quota
        Route::post('/quota-simpan', [QuotaController::class, 'quota_simpan']);
        Route::get('/quota', [QuotaController::class, 'get_quota']);
        Route::post('/quota-hapus', [QuotaController::class, 'quota_hapus']);

        Route::post('/buat-do', [DoControleller::class, 'buat_do']);
        Route::get('/ambil-do', [DoControleller::class, 'ambil_do']);

        //penjualan
        Route::post('/simpan-penjualan', [PenjualanPenjualanController::class, 'simpan_penjualan']);
        Route::post('/catat', [PenjualanPenjualanController::class, 'catat']);
        Route::post('/hapus-penjualan', [PenjualanPenjualanController::class, 'hapus_penjualan']);
        Route::get('/ambil-penjualan', [PenjualanPenjualanController::class, 'ambil_penjualan']);
        Route::post('/penjualan-terakhir', [PenjualanPenjualanController::class, 'penjualan_terakhir']);
        Route::post('/informasi-umum', [PenjualanPenjualanController::class, 'informasi_umum']);
        Route::post('/selesai-antar', [PenjualanPenjualanController::class, 'selesai_antar']);
        Route::post('/batal-antar', [PenjualanPenjualanController::class, 'batal_antar']);
        Route::post('/tagih', [PenjualanPenjualanController::class, 'tagih']);
        Route::post('/batal-tagih', [PenjualanPenjualanController::class, 'batal_tagih'])->middleware('role.api:Super Admin');
        Route::post('/status-tagihan', [PenjualanPenjualanController::class, 'status_tagihan']);
        Route::post('/status-create', [PenjualanPenjualanController::class, 'status_create'])->middleware('permission.api:status create');
        Route::post('/cek-kewajiban', [PenjualanPenjualanController::class, 'cek_kewajiban']);
        Route::post('/daftar-transfer', [PenjualanPenjualanController::class, 'daftar_transfer']);

        //Bank
        Route::post('/report', [PembayaranController::class, 'report']);
        Route::post('/inquiry', [PembayaranController::class, 'inquiry']);
        Route::post('/create', [PembayaranController::class, 'create']);
        Route::post('/delete', [PembayaranController::class, 'delete']);


        //Perubahan
        Route::post('/setujui', [PersetujuanController::class, 'setujui']);


        //Pembayaran
        Route::post('/pembayaran', [PembayaranController::class, 'index']);

        Route::post('/notif', [NotifController::class, 'tes_notif']);
    });

    Route::middleware(['auth:sanctum', 'abilities:pangkalan2'])->group(function () {
        Route::get('/logout-pangkalan', [AuthController::class, 'logout_pangkalan']);
        Route::get('/k', [KController::class, 'ktp']);
        Route::post('/k', [KController::class, 'simpan_k']);

        Route::get('/dasboard', [DasboardController::class, 'index']);

        Route::get('/transaksi/{bulan}/{tahun}', [TransaksiController::class, 'getTransaksi']);
        Route::get('/transaksiv2/{bulan}/{tahun}', [TransaksiV2Controller::class, 'ambil_penjualan_pangkalan']);
        Route::get('cek-status-pangkalan', [TransaksiV2Controller::class, 'cek_status_pangkalan']);
    });



    //Route::post(config('pembayaran.url_notif_webhook'), [NotifikasiPembayaranController::class, 'notifikasi_bri']);

    Route::post('/onesig', [OnesignalController::class, 'simpan_player']);
    Route::get('/waktu', [OnesignalController::class, 'waktu']);
});
