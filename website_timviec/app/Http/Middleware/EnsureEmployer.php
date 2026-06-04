<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureEmployer — Chặn user không phải 'employer' (nhà tuyển dụng).
 */
class EnsureEmployer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->user_type !== 'employer') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chỉ nhà tuyển dụng mới được phép truy cập.'], 403);
            }
            abort(403, 'Chỉ nhà tuyển dụng mới được phép truy cập chức năng này.');
        }

        return $next($request);
    }
}
