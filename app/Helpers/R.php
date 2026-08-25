<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class R
{
    public static function sukses(
        string $pesan,
        int $statusCode = 201
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'pesan' => $pesan,
        ], $statusCode);
    }
    public static function gagal(
        string $pesan,
        int $statusCode = 404
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'pesan' => $pesan,
        ], $statusCode);
    }
    public static function data(
        string $pesan,
        mixed  $data,
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'pesan' => $pesan,
            'data' => $data,
        ], $statusCode);
    }
}