<?php


namespace App\Services;

use App\Exceptions\GagalE;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;



class BRIServices
{

    protected string $baseUrl, $clientId, $timestamp, $patner_id, $x_partner_id;
    protected ?string $token = null;


    public function __construct()
    {
        $this->baseUrl = config('pembayaran.base_url');
        $this->clientId = config('pembayaran.client_id');
        $this->patner_id = config('pembayaran.patner_id');
        $this->x_partner_id = config('pembayaran.x_partner_id');

        $this->timestamp = $timestamp = Carbon::now()->format('Y-m-d\TH:i:s.vP');
        $this->token = $this->getToken();
    }

    public function buatSignaturToken()
    {
        $stringToSign = $this->clientId . '|' . $this->timestamp;
        $privateKey = file_get_contents(config('pembayaran.private_key_bri'));
        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }



    public function buatSignaturAkses(string $body, string $path, string $method = 'POST')
    {
        $bodyHash = hash('sha256', $body);
        $token = $this->token;
        $timestamp = $this->timestamp;
        $stringToSign = strtoupper($method) . ':' . $path . ':' . $token . ':' . strtolower($bodyHash) . ':' . $timestamp;
        return $signature = base64_encode(hash_hmac('sha512', $stringToSign, config('pembayaran.client_secret'), true));
    }



    public function create(string $customer_no, string $nama, string $transaksi_id, string $jumlah, string $keterangan = "-")
    {
        $expiredDate = Carbon::now()->addMonthsNoOverflow(12)->format('Y-m-d\TH:i:sP');
        $path = "/snap/v1.0/transfer-va/create-va";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "customerNo" => $customer_no,
            "virtualAccountNo" => "   " . $this->patner_id . $customer_no,
            "virtualAccountName" => $nama,
            "trxId" => $transaksi_id,
            "totalAmount" => [
                "value" => $jumlah,
                "currency" => "IDR"
            ],
            "expiredDate" => $expiredDate,
            "additionalInfo" => [
                "description" => $keterangan
            ]
        ];

        $jsonBody = json_encode($body);

        $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl . $path);

        Log::channel('bri')->info('CREATE VA', [
            'url'        => $path,
            'request'    => $body,
            'http_code'  => $hit->status(),
            'response'   => $hit->json(),
        ]);
        return $hit;
    }





    public function updateVA(string $customer_no, string $nama, string $transaksi_id, string $jumlah, string $keterangan = "-")
    {
        $expiredDate = Carbon::now()->addMonthsNoOverflow(12)->format('Y-m-d\TH:i:sP');
        $path = "/snap/v1.0/transfer-va/update-va";
        // $path = "/snap/v1.0/transfer-va/create-va";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "customerNo" => $customer_no,
            "virtualAccountNo" => "   " . $this->patner_id . $customer_no,
            "virtualAccountName" => $nama,
            "trxId" => $transaksi_id,
            "totalAmount" => [
                "value" => $jumlah,
                "currency" => "IDR"
            ],
            "expiredDate" => $expiredDate,
            "additionalInfo" => [
                "description" => $keterangan
            ]
        ];

        $jsonBody = json_encode($body);
        $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path, "PUT"),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->put($this->baseUrl . $path);

        Log::channel('bri')->info('updateVA', [
            'url'        => $path,
            'request'    => $body,
            'http_code'  => $hit->status(),
            'response'   => $hit->json(),
        ]);
        return $hit;
    }


    public function updateStatusVA(string $customer_no, string $transaksi_id, string $status = "Y")
    {
        $path = "/snap/v1.0/transfer-va/update-status";
        // $path = "/snap/v1.0/transfer-va/create-va";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "customerNo" => $customer_no,
            "virtualAccountNo" => "   " . $this->patner_id . $customer_no,
            "trxId" => $transaksi_id,
            "paidStatus" => $status,
        ];

        $jsonBody = json_encode($body);
        $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path, "PUT"),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->put($this->baseUrl . $path);
        Log::channel('bri')->info('updateStatusVA', [
            'url'        => $path,
            'request'    => $body,
            'http_code'  => $hit->status(),
            'response'   => $hit->json(),
        ]);
        return $hit;
    }


    public function inquiryVA(string $customer_no, string $transaksi_id)
    {
        $path = "/snap/v1.0/transfer-va/inquiry-va";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "customerNo" => $customer_no,
            "virtualAccountNo" => "   " . $this->patner_id . $customer_no,
            "trxId" => $transaksi_id,
        ];

        $jsonBody = json_encode($body);
        $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl . $path);
        return $hit;
    }

    public function deleteVA(string $customer_no)
    {
        $path = "/snap/v1.0/transfer-va/delete-va";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "customerNo" => $customer_no,
            "virtualAccountNo" => "   " . $this->patner_id . $customer_no,
        ];

        $jsonBody = json_encode($body);
        $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path, "DELETE"),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->delete($this->baseUrl . $path);
        Log::channel('bri')->info('deleteVA', ['request' => $jsonBody, 'respon' => json_encode($hit->json())]);
        return $hit;
    }

    public function laporan($tgl)
    {
        $path = "/snap/v1.0/transfer-va/report";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "startDate" => "$tgl",
            "startTime" => "00:00:00+07:00",
            // "endDate" => "2026-08-04",
            "endTime" => "23:59:59+07:00",
            // "additionalInfo" => []
        ];

        $jsonBody = json_encode($body);
        $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl . $path);
        return $hit;
    }

    public function status(string $customer_no, string $inquiryRequestId)
    {
        $path = "/snap/v1.0/transfer-va/status";
        $body = [
            "partnerServiceId" => "   " . $this->patner_id,
            "customerNo" => $customer_no,
            "virtualAccountNo" => "   " . $this->patner_id . $customer_no,
            "inquiryRequestId" => "$inquiryRequestId",
        ];

        $jsonBody = json_encode($body);
        return $hit = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer " . $this->token,
            'X-SIGNATURE' => $this->buatSignaturAkses($jsonBody, $path),
            'X-PARTNER-ID' => $this->x_partner_id,
            'X-TIMESTAMP' => $this->timestamp,
            'CHANNEL-ID' => '00002',
            'X-EXTERNAL-ID' => '334547',
        ])->withBody($jsonBody, 'application/json')
            ->post($this->baseUrl . $path);
        return $hit;
    }




    public function getToken()
    {
        $path = "/snap/v1.0/access-token/b2b";
        if (Cache::has('bri_token')) {
            return Cache::get('bri_token');
        }

        try {
            $ambilToken = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-SIGNATURE' => $this->buatSignaturToken(),
                'X-CLIENT-KEY' => $this->clientId,
                'X-TIMESTAMP' => $this->timestamp,
            ])->post($this->baseUrl . $path, [
                'grantType' => 'client_credentials'
            ]);

            $result = $ambilToken->json();
            if (
                !isset($result['accessToken']) ||
                !isset($result['expiresIn'])
            ) {
                throw new GagalE('Format response token tidak valid', 400);
            }

            $token = $result['accessToken'];
            $expired = $result['expiresIn'];

            Cache::put('bri_token', $token, now()->addSeconds($expired - 120));
            return $token;
        } catch (\Throwable $e) {
            logger()->error('Gagal ambil token BRI', [
                'message' => $e->getMessage(),
            ]);

            throw new GagalE('Gagal mengambil token BRI', 500);
            // return response()->json([
            //     'status' => false,
            //     'pesan' => 'Gagal mengambil token BRI',
            //     'error' => $e->getMessage(),
            // ], 500);
        }
    }
}
