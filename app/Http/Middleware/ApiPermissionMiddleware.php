<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = Auth::user();

        // Cek apakah user punya permission tertentu
        if (!$user->can($permission)) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Akses tidak diberikan...',
            ], 403);
        }

        return $next($request);
    }
}
