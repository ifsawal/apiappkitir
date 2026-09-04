<?php

namespace App\Http\Controllers\api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\AWebHookToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OauthController extends Controller
{

    public function validasiSignatureToken(
        string $clientId,
        string $timestamp,
        string $signature
    ): bool {
        $stringToSign = $clientId . '|' . $timestamp;
        $publicKey = file_get_contents(config('pembayaran.BRI_NOTIF_public_key_bri'));
        $signatureBinary = base64_decode($signature, true);
        if ($signatureBinary === false) {
            return false;
        }
        $result = openssl_verify(
            $stringToSign,
            $signatureBinary,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );
        return $result === 1;
    }


    public function token(Request $request)
    {
        // Cek client ID
        $contentType = $request->header('Content-Type');
        if ($contentType !== 'application/json') {
            return response()->json([
                'message' => 'Content-Type must be application/json'
            ], 415);
        }


        if ($request->header('X-CLIENT-KEY') !== config('pembayaran.BRI_NOTIF_CLIENT_ID')) {
            return response()->json([
                'message' => 'Invalid client credentials'
            ], 401);
        }

        $timestamp = $request->header('X-TIMESTAMP');
        if (!preg_match(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}[+-]\d{2}:\d{2}$/',
            $timestamp
        )) {
            return response()->json([
                'message' => 'Invalid X-TIMESTAMP format'
            ], 400);
        }


        $timestamp = $request->header('X-SIGNATURE');
        if (!$this->validasiSignatureToken(
            $request->header('X-CLIENT-KEY'),
            $timestamp,
            $request->header('X-SIGNATURE')
        )) {
            return response()->json([
                'message' => 'Invalid signature'
            ], 401);
        }


        // Cek client secret
        // if (!hash_equals(
        //     (string) config('pembayaran.BRI_NOTIF_CLIENT_SECRET'),
        //     (string) $request->client_secret
        // )) {
        //     return response()->json([
        //         'message' => 'Invalid client credentials'
        //     ], 401);
        // }

        // Token asli
        $token = Str::random(80);

        // Waktu expired
        $expiresAt = now()->addSeconds(
            (int) config('pembayaran.BRI_NOTIF_TOKEN_EXPIRES')
        );

        // Simpan HASH token, bukan token aslinya
        AWebHookToken::create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => (string) max(0, floor(now()->diffInSeconds($expiresAt, false))),
            // 'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function notif(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Webhook received'
        ]);
    }
}
