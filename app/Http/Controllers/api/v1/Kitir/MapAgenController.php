<?php

namespace App\Http\Controllers\api\v1\Kitir;

use App\Models\Pangkalan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MapAgenController extends Controller
{
    public function script($id)
    {

        $pangkalan = Pangkalan::where('id_pang', $id)->first();
        return response()->json([
            'status' => true,
            'data' => [
                    'nama' => $pangkalan->nama,
                    'id_reg' => $pangkalan->id_sim,
                    'briva' => 'Briva ' . config('app.briva') . $pangkalan->no_briva,
                    'link_map' => "https://subsiditepatlpg.mypertamina.id/merchant-login",  //link login merchant
                    'link_beranda' => "https://subsiditepatlpg.mypertamina.id/merchant/app",  //link login merchant
                    'link_nama_ktp' => "https://subsiditepatlpg.mypertamina.id/merchant/app/sale",  //link muncul nama pelanggan
                    'email' => $pangkalan->email,
                    'pin' => $pangkalan->pin,

                    //script klik catat penjualan
                    'klik_catat' => 'var el = Array.from(document.querySelectorAll(\'div\'))     .find(e => e.innerText.trim() === "Catat Penjualan");  if (el) {     el.click(); }',
                    'lanjut_penjualan' => 'var btn = document.querySelector(\'[data-testid="btnCheckNik"]\'); if (btn) {     btn.click(); }',
                    //script masukkan NIK ke input
                    'masuk_nik' => 'function setReactValue(el, value) {     const setter = Object.getOwnPropertyDescriptor(el.__proto__, "value").set;     setter.call(el, value);     el.dispatchEvent(new Event("input", { bubbles: true }));     el.dispatchEvent(new Event("change", { bubbles: true })); }  var nikInput = document.querySelector(\'input[placeholder="Masukkan 16 digit NIK Pelanggan"]\');  if (nikInput) {     setReactValue(nikInput, "',
                    'masuk_nik2' => '"); }',

                    'cek_pesanan' => 'var btn = document.querySelector(\'[data-testid="btnCheckOrder"]\'); if (btn) {     btn.click(); }',
                    'proses_penjualan' => 'var btn = document.querySelector(\'[data-testid="btnPay"]\'); if (btn) {     btn.click(); }',

                    'kembali_utama' => 'var links = Array.from(document.querySelectorAll(\'a\'));
var link = links.find(l => l.textContent.trim() === "KEMBALI KE HALAMAN UTAMA");
if (link) {
    link.click();
}',
                    'tekan_back' => 'document.querySelector(\'[data-testid="btnChangeBuyer"]\').click();',
                    //proses isi username dan pin dan klik tombol masuk
                    'j1' => 'function setReactInputValue(el, value) {
    const setter = Object.getOwnPropertyDescriptor(el.__proto__, \'value\').set;
    setter.call(el, value); // ubah via setter React
    el.dispatchEvent(new Event(\'input\', { bubbles: true }));
    el.dispatchEvent(new Event(\'change\', { bubbles: true }));
}

// Input nomor HP / Email
let input1 = document.querySelector(\'input[placeholder="Masukkan Nomor Ponsel atau Email"]\');
if (input1) {
    setReactInputValue(input1, "',


                    'j2' => '");
}

// Input PIN
let input2 = document.querySelector(\'input[placeholder="Masukkan nomor PIN Anda"]\');
if (input2) {
    setReactInputValue(input2, "',

                    'j3' => '");
}

// Klik tombol MASUK
let btn = Array.from(document.querySelectorAll(\'button\'))
    .find(b => b.innerText.trim().toUpperCase() === "MASUK");

if (btn) {
    btn.click();
}',
                ],
        ], 202);
    }
}
