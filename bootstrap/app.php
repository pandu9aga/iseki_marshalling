<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Env;

if (php_sapi_name() === 'apache2handler' && ($_SERVER['WINDIR'] ?? false)) {
    Env::disablePutenv();
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('member/record/*') || $request->is('member/records/*')) {
                return redirect()->route('member.record.create')
                    ->with('error', 'Halaman atau data record tidak ditemukan / telah dihapus. Silahkan scan QR kanban baru.');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if (in_array($e->getStatusCode(), [409, 419])) {
                \Illuminate\Support\Facades\Auth::guard('admin')->logout();
                \Illuminate\Support\Facades\Auth::guard('member')->logout();
                \Illuminate\Support\Facades\Auth::guard('perakitan')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Sesi login telah diperbarui. Silahkan login kembali.');
            }
        });
    })->create();
