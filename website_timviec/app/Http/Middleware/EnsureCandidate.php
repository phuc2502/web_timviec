<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureCandidate — Chặn user không phải 'employee' (ứng viên).
 */
class EnsureCandidate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->user_type !== 'employee') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chỉ ứng viên mới được phép truy cập.'], 403);
            }
            abort(403, 'Chỉ ứng viên mới được phép truy cập chức năng này.');
        }

        return $next($request);
    }
}
