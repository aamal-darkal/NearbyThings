<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsNotBlocked;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // المستخدم المسجّل دخوله اذا فتح صفحات الدخول أو التسجيل منوديه عالملف الشخصي
        $middleware->redirectUsersTo(fn () => route('profile.show'));

        // المستخدم المحظور بينطلع من حسابه بأي طلب
        $middleware->web(append: [EnsureUserIsNotBlocked::class]);
        $middleware->api(append: [EnsureUserIsNotBlocked::class]);

        // اسم مختصر لحماية صفحات المشرف
        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
