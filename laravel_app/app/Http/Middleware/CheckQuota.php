<?php

namespace App\Http\Middleware;

use App\Services\QuotaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckQuota
{
    public function __construct(
        private QuotaService $quotaService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->is_admin) {
            return $next($request);
        }

        if (!$this->quotaService->canCreateListing($user)) {
            return response()->json([
                'message' => 'Bạn đã đạt giới hạn tin đăng. Vui lòng nâng cấp gói hoặc đóng bớt tin cũ.',
                'current_active' => $this->quotaService->getActiveListingCount($user),
                'max_allowed' => $this->quotaService->getQuotaLimit($user),
            ], 403);
        }

        return $next($request);
    }
}
