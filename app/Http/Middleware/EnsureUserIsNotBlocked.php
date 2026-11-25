<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * اذا المشرف حظر المستخدم وهو مسجل دخول، منطلعه من الحساب
 */
class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_blocked) {
            // طلبات الـ API: منحذف التوكن ومنرجع خطأ
            if ($request->is('api/*')) {
                $user->currentAccessToken()?->delete();

                return response()->json(['message' => 'تم حظر حسابك من قبل إدارة الموقع'], 403);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'تم حظر حسابك من قبل إدارة الموقع',
            ]);
        }

        return $next($request);
    }
}
