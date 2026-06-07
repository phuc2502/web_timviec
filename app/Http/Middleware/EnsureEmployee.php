<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureEmployee — Middleware chặn các user_type không phải 'employee'.
 *
 * Sử dụng kết hợp sau middleware 'auth' để đảm bảo user đã đăng nhập
 * trước khi kiểm tra loại tài khoản.
 */
class EnsureEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->user_type !== 'employee') {
            abort(403, 'Chỉ ứng viên (employee) mới được phép truy cập chức năng này.');
        }

        return $next($request);
    }
}
