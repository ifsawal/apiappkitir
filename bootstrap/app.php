<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // tambahkan ini 👇
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'permission.api' => \App\Http\Middleware\ApiPermissionMiddleware::class,
            'role.api' => RoleMiddleware::class,
        ]);
    })
    // ->withProviders([
    //     'users' => [
    //         'driver' => 'eloquent',
    //         'model' => App\Models\User::class,
    //     ],
    //     'pangkalan2' => [ // ✅ provider baru
    //         'driver' => 'eloquent',
    //         'model' => App\Models\Pangkalan2::class,
    //     ],
    // ])

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Illuminate\Validation\ValidationException $e, $request) {
            $pesan = config('app.debug')
                ? $e->getMessage()
                : 'Terjadi kesalahan internal.';
            return response()->json([
                'status' => false,
                'pesan' => $pesan,
                'kesalahan' => $e->errors(),
            ], $e->status);
        });
    })->create();
