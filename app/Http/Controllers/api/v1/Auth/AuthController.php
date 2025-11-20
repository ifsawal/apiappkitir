<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\v1\Auth\PangkalanResource;
use App\Models\Pangkalan;
use App\Models\Pangkalan2;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login_pangkalan(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255|email',
            'password' => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 428);
        }


        if (!Auth::guard('pangkalan2')->attempt($request->only('email', 'password'))) {
            return response()
                ->json(['sukses' => false, 'pesan' => 'Login gagal...'], 401);
        }

        $user = Auth::guard('pangkalan2')->user();

        $pangkalan = Pangkalan::where('id_pang', $user->pangkalan_id)->first();
        // $pangkalan = Pangkalan2::where('email', $r->email)->first();
        $token = $user->createToken('auth_token', ['pangkalan2'])->plainTextToken;
        return response()
            ->json([
                'pesan' => "Login Berhasil...",
                'token' => $token,
                'user' => $user->name,
                'detil' => [
                    'nama' => $pangkalan->nama,
                    'id_reg' => $pangkalan->id_sim,
                    'briva' => 'Briva 13489'.$pangkalan->no_briva,
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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255|email',
            'password' => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 428);
        }

        if (!Auth::guard('api')->attempt($request->only('email', 'password'))) {
            return response()
                ->json(['sukses' => false, 'pesan' => 'Login gagal...'], 401);
        }

        // return $user = Auth::guard($guad)->user();

        $user = User::where('email', $request->email)->first();

        $role = $user->getRoleNames();
        $col = collect($user->getAllPermissions());
        $permisi = $col->map(function ($col) {
            return collect($col->toArray())
                ->only(['id', 'name'])
                ->all();
        });

        $pangkalan = Pangkalan::all();
        $a = PangkalanResource::collection($pangkalan);

        $token = $user->createToken('auth_token', ['admin'])->plainTextToken;
        return response()
            ->json([
                'pesan' => "Login Berhasil",
                'token' => $token,
                'role' => $role,
                'permisi' => $permisi,
                'user' => $user->name,
                'pangkalan' => $a
            ], 202);
    }



    public function logout()
    {

        Auth::user()->tokens()->delete();
        return response()
            ->json([
                'sukses' => true,
                'pesan' => "Logout sukses...",
            ], 204);
    }

    public function logout_pangkalan()
    {
        Auth::user()->tokens()->delete();
        return response()
            ->json([
                'sukses' => true,
                'pesan' => "Logout sukses...",
            ], 204);
    }
}
