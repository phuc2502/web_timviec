<?php

namespace App\Http\Middleware;

use App\Services\RateLimitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRateLimit
{
    public function __construct(
        private RateLimitService $rateLimitService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->is_admin) {
            return $next($request);
        }

        if (!$this->rateLimitService->canCreateListing($user)) {
            return response()->json([
                'message' => 'Bạn đã vượt quá giới hạn tạo tin trong 24 giờ.',
                'remaining' => $this->rateLimitService->getRemainingAttempts($user),
                'reset_at' => $this->rateLimitService->getResetTime($user),
            ], 429);
        }

        return $next($request);
    }
}
