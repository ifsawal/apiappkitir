<?php

namespace App\Services;

use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class NotifServices
{

    protected $fcmToken, $projectId;

    public function __construct()
    {
        $this->projectId = config('fcm.project_id');
    }

    public function ambilTokenFCM()
    {
        if (Cache::has('fcm_token')) {
            return Cache::get('fcm_token');
        }

        $path = storage_path('key/fcm/private_key.json');
        try {
            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $path
            );
            $tokenResponse = $credentials->fetchAuthToken();

            if (!isset($tokenResponse['access_token'])) {
                throw new Exception('Format response token FCM tidak valid');
            }
            $token = $tokenResponse['access_token'];
            $expired = $tokenResponse['expires_in'];

            Cache::put(
                'fcm_token',
                $token,
                now()->addSeconds($expired - 120)
            );
            return $token;
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil token FCM: ' . $e->getMessage());
            return null;
        }
    }

    public function kirimFCM(string $fcmToken, string $title, string $body, $data = [])
    {

        $token = $this->ambilTokenFCM();
        $projectId = $this->projectId;

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => array_merge(
                    
                    [
                    'click_action' => 'NOTIFICATION_CLICK',
                    ],$data),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        $response = Http::withToken($token)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        return $response->json();
    }
}
