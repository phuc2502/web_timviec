<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureAdmin — Chặn các tài khoản không phải Admin.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || ($user->user_type !== 'admin' && !$user->is_admin)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bạn không có quyền truy cập khu vực quản trị.'], 403);
            }
            abort(403, 'Bạn không có quyền truy cập khu vực quản trị.');
        }

        return $next($request);
    }
}
