<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingView;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsService
{
    /**
     * Ghi nhận lượt xem listing.
     */
    public function trackView(Listing $listing, ?User $user, Request $request): void
    {
        ListingView::create([
            'listing_id' => $listing->id,
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'traffic_source' => $request->header('referer'),
            'action_type' => 'view',
        ]);
    }

    /**
     * Ghi nhận lượt click ứng tuyển.
     */
    public function trackApplyClick(Listing $listing, ?User $user, Request $request): void
    {
        ListingView::create([
            'listing_id' => $listing->id,
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'traffic_source' => $request->header('referer'),
            'action_type' => 'apply_click',
        ]);
    }

    /**
     * Lấy analytics chi tiết cho 1 listing.
     */
    public function getListingAnalytics(Listing $listing, int $days = 7): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        // Total views
        $totalViews = ListingView::where('listing_id', $listing->id)
            ->where('action_type', 'view')
            ->count();

        // Total apply clicks
        $totalApplyClicks = ListingView::where('listing_id', $listing->id)
            ->where('action_type', 'apply_click')
            ->count();

        // Conversion rate
        $conversionRate = $totalViews > 0
            ? round(($totalApplyClicks / $totalViews) * 100, 2)
            : 0;

        // Views by day
        $viewsByDay = ListingView::where('listing_id', $listing->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, action_type, COUNT(*) as count')
            ->groupBy('date', 'action_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($group) {
                return [
                    'date' => $group->first()->date,
                    'views' => $group->where('action_type', 'view')->sum('count'),
                    'apply_clicks' => $group->where('action_type', 'apply_click')->sum('count'),
                ];
            })
            ->values();

        // Top traffic sources
        $topSources = ListingView::where('listing_id', $listing->id)
            ->whereNotNull('traffic_source')
            ->selectRaw('traffic_source as source, COUNT(*) as count')
            ->groupBy('traffic_source')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'listing_id' => $listing->id,
            'total_views' => $totalViews,
            'total_apply_clicks' => $totalApplyClicks,
            'conversion_rate' => $conversionRate,
            'views_by_day' => $viewsByDay,
            'top_traffic_sources' => $topSources,
        ];
    }

    /**
     * Thống kê tổng hợp toàn hệ thống (Admin only).
     */
    public function getSystemOverview(): array
    {
        $startOfWeek = now()->startOfWeek();

        return [
            'new_listings_this_week' => Listing::where('created_at', '>=', $startOfWeek)->count(),
            'total_views' => ListingView::where('action_type', 'view')->count(),
            'pending_review_count' => Listing::where('status', 'pending_review')->count(),
            'avg_conversion_rate' => $this->calculateAvgConversionRate(),
        ];
    }

    private function calculateAvgConversionRate(): float
    {
        $stats = ListingView::selectRaw('
            SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) as total_views,
            SUM(CASE WHEN action_type = "apply_click" THEN 1 ELSE 0 END) as total_clicks
        ')->first();

        if ($stats->total_views > 0) {
            return round(($stats->total_clicks / $stats->total_views) * 100, 2);
        }

        return 0;
    }
}
