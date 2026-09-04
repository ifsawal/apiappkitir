<?php

namespace App\Http\Middleware;

use App\Models\AWebHookToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookBriMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Missing access token'
            ], 401);
        }

        $tokenHash = hash('sha256', $token);

        $accessToken = AWebHookToken::where(
            'token_hash',
            $tokenHash
        )->first();

        if (!$accessToken) {
            return response()->json([
                'message' => 'Invalid access token'
            ], 401);
        }

        if ($accessToken->expires_at->isPast()) {
            return response()->json([
                'message' => 'Access token expired'
            ], 401);
        }
        return $next($request);
    }
}
