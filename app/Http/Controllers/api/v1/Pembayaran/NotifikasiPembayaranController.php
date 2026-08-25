<?php

namespace App\Http\Controllers\api\v1\Pembayaran;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifikasiPembayaranController extends Controller
{

    protected $token = "334$$#4564564651#$6515645##6465";
    protected $client_secret = "client_secret_334$$#4564564651#$6515645##6465";

    public function buatSignaturAkses(string $body, string $path, string $method, string $timestamp, string $token)
    {
        
        $bodyHash = hash('sha256', $body);
        $stringToSign = strtoupper($method) . ':' . $path . ':' . $token . ':' . strtolower($bodyHash) . ':' . $timestamp;
        return $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->client_secret, true));
    }

    public function notifikasi_bri(Request $r)
    {

        Log::channel('bri_notif')->info('Request', [
            'headers' => $r->headers->all(),
            'body'    => json_decode($r->getContent(), true),
        ]);

        $requiredHeaders = [
            'Authorization',
            'X-TIMESTAMP',
            'X-SIGNATURE',
            'X-PARTNER-ID',
            'CHANNEL-ID',
            'X-EXTERNAL-ID',
            'Content-Type',
        ];

        foreach ($requiredHeaders as $header) {
            if (!$r->hasHeader($header)) {
                return response()->json([
                    'responseCode' => '4002702',
                    'responseMessage' => "Invalid Mandatory Field: {$header}"
                ], 200);
            }
        }
        

        if ($r->header('Authorization') != 'Bearer ' . $this->token) {
            return response()->json([
                'responseCode' => '4012701',
                'responseMessage' => "Invalid Token (B2B)"
            ], 200);
        }

        if ($r->header('X-PARTNER-ID') != config('pembayaran.x_partner_id')) {
            return response()->json([
                'responseCode' => '4012700',
                'responseMessage' => "Unauthorized. Client"
            ], 200);
        }

        try {
            $timestamp = Carbon::parse($r->header('X-TIMESTAMP'));
        } catch (\Exception $e) {
            return response()->json([
                'responseCode' => '400',
                'responseMessage' => 'Format X-TIMESTAMP tidak valid.'
            ], 200);
        }

        $signatur = $this->buatSignaturAkses($r->getContent(), config('pembayaran.url_notif_webhook'), $r->method(), $r->header('X-TIMESTAMP'), $this->token);
        Log::channel('bri_notif')->info('Cek', [
            'kirim' => $r->header('X-SIGNATURE'),
            'cek'    => $signatur,
        ]);
        if (!hash_equals($r->header('X-SIGNATURE'), $signatur)) {
            return response()->json([
                'responseCode' => '4012700',
                'responseMessage' => 'Unauthorized. Signature'
            ], 200);
        }


        

        return response()->json(['status' => 'OK']);
    }
}
