<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicListingController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Tìm kiếm và lọc danh sách tin tuyển dụng đang hoạt động (Public).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Listing::active()->with(['category', 'skills', 'user']);

        // Filter by keyword
        if ($keyword = $request->input('keyword')) {
            // Using scopeSearch (which uses FullText index)
            // Fallback to LIKE if we are in testing or sqlite environment
            try {
                $query->search($keyword);
            } catch (\Exception $e) {
                $query->where(function($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            }
        }

        // Filter by category_id
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by job_type (comma separated or array)
        if ($jobType = $request->input('job_type')) {
            $types = is_array($jobType) ? $jobType : explode(',', $jobType);
            $query->whereIn('job_type', $types);
        }

        // Filter by level
        if ($level = $request->input('level')) {
            $levels = is_array($level) ? $level : explode(',', $level);
            $query->whereIn('level', $levels);
        }

        // Filter by address
        if ($address = $request->input('address')) {
            $query->where('address', 'like', '%' . $address . '%');
        }

        // Filter by salary_min
        if ($request->filled('salary_min')) {
            $query->where(function($q) use ($request) {
                $q->where('salary_min', '>=', $request->salary_min)
                  ->orWhere('is_negotiable', true);
            });
        }

        // Filter by salary_max
        if ($request->filled('salary_max')) {
            $query->where(function($q) use ($request) {
                $q->where('salary_max', '<=', $request->salary_max)
                  ->orWhere('is_negotiable', true);
            });
        }

        // Filter by skills (comma separated or array of skill IDs)
        if ($skills = $request->input('skills')) {
            $skillIds = is_array($skills) ? $skills : explode(',', $skills);
            $query->whereHas('skills', function($q) use ($skillIds) {
                $q->whereIn('skills.id', $skillIds);
            });
        }

        // Sorting
        $sortParam = $request->input('sort', 'created_at,desc');
        $parts = explode(',', $sortParam);
        $sortField = $parts[0] ?? 'created_at';
        $sortOrder = $parts[1] ?? 'desc';

        $allowedSortFields = ['created_at', 'salary_min', 'salary_max', 'title'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min((int)$request->input('per_page', 20), 50);
        $listings = $query->paginate($perPage);

        // Append formatted_salary and days_until_expiry via resource-like attributes
        $listings->getCollection()->transform(function($listing) {
            $listing->formatted_salary = $listing->formatted_salary;
            $listing->days_until_expiry = $listing->days_until_expiry;
            return $listing;
        });

        return response()->json($listings);
    }

    /**
     * Chi tiết tin tuyển dụng + ghi nhận lượt xem.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $listing = Listing::active()->with(['category', 'skills', 'user'])->findOrFail($id);

        // Record view asynchronously or synchronously
        $this->analyticsService->trackView($listing, $request->user(), $request);

        $listing->formatted_salary = $listing->formatted_salary;
        $listing->days_until_expiry = $listing->days_until_expiry;

        return response()->json($listing);
    }

    /**
     * Ghi nhận click ứng tuyển.
     */
    public function applyClick(Request $request, int $id): JsonResponse
    {
        $listing = Listing::active()->findOrFail($id);

        $this->analyticsService->trackApplyClick($listing, $request->user(), $request);

        return response()->json([
            'message' => 'Đã ghi nhận lượt click ứng tuyển thành công.'
        ]);
    }
}
