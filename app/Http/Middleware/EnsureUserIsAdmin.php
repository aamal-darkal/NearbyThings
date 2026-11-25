<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * صفحات لوحة المشرف: بس للمستخدم اللي عنده is_admin
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            abort(403, 'هذه الصفحة للمشرفين فقط');
        }

        return $next($request);
    }
}
