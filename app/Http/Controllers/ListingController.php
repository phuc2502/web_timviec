<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFilterRequest;
use App\Http\Resources\ListingResource;
use App\Http\Resources\SkillResource;
use App\Services\ListingSearchService;
use Illuminate\Http\JsonResponse;

class ListingController extends Controller
{
    public function __construct(
        private readonly ListingSearchService $service
    ) {}

    /**
     * GET /api/listings/search
     *
     * Tìm kiếm và lọc tin tuyển dụng.
     * Trả về Result_Set phân trang theo định dạng JSON (Req 16.1).
     */
    public function index(SearchFilterRequest $request): JsonResponse
    {
        $results = $this->service->search($request->validated());

        return ListingResource::collection($results)->response();
    }

    /**
     * GET /api/skills
     *
     * Trả về danh sách tất cả kỹ năng, không phân trang (Req 16.2).
     */
    public function skills(): JsonResponse
    {
        $skills = $this->service->getSkills();

        return SkillResource::collection($skills)->response();
    }

    /**
     * GET /api/listings/cities
     *
     * Trả về danh sách địa chỉ distinct từ Active_Listing, sắp xếp alphabet (Req 16.3).
     */
    public function cities(): JsonResponse
    {
        return response()->json($this->service->getCities());
    }
}
