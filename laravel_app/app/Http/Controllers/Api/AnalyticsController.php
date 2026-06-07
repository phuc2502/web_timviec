<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Lấy thống kê của một tin tuyển dụng (chỉ chủ sở hữu tin hoặc admin).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $listing = Listing::findOrFail($id);

        // Authorization check
        if ($listing->user_id !== $request->user()->id && !$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        $request->validate([
            'days' => 'nullable|integer|min:1|max:90',
        ]);

        $days = $request->input('days', 7);

        $analytics = $this->analyticsService->getListingAnalytics($listing, $days);

        return response()->json($analytics);
    }

    /**
     * Lấy thống kê tổng quan hệ thống (chỉ admin).
     */
    public function overview(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        $overview = $this->analyticsService->getSystemOverview();

        return response()->json($overview);
    }
}
