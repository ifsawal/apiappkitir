<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class GagalE extends Exception
{
    public function __construct(
        string $message,
        public int $statusCode = 400
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'pesan' => $this->getMessage(),
        ], $this->statusCode);
    }
}
