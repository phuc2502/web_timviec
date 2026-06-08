<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Skill;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListingSearchService
{
    /**
     * Tìm kiếm và lọc tin tuyển dụng.
     *
     * @param  array $filters  Mảng đã validated từ SearchFilterRequest::validated()
     * @return LengthAwarePaginator
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $keyword    = isset($filters['keyword']) ? trim($filters['keyword']) : null;
        $hasKeyword = $keyword !== null && $keyword !== '';

        // Base query: chỉ lấy Active_Listing, eager load employer + skills
        $query = Listing::active()
            ->with(['employer', 'skills'])
            ->select('listings.*');

        // 1. Keyword filter (FULLTEXT)
        if ($hasKeyword) {
            $query = $this->applyKeywordFilter($query, $keyword);
        }

        // 2. Job type filter
        if (!empty($filters['job_type'])) {
            $query->where('listings.job_type', strtolower($filters['job_type']));
        }

        // 3. Work mode filter
        if (!empty($filters['work_mode'])) {
            $query->where('listings.work_mode', strtolower($filters['work_mode']));
        }

        // 4. Skill filter
        $skillIds = $filters['skills'] ?? [];
        if (!empty($skillIds)) {
            $mode  = $filters['skill_mode'] ?? 'and';
            $query = $this->applySkillFilter($query, $skillIds, $mode);
        }

        // 5. Address filter
        if (!empty($filters['address'])) {
            $escaped = $this->escapeLike($filters['address']);
            $query->whereRaw('listings.address LIKE ?', ["%{$escaped}%"]);
        }

        // 6. City filter
        if (!empty($filters['city'])) {
            $escaped = $this->escapeLike($filters['city']);
            $query->whereRaw('listings.address LIKE ?', ["%{$escaped}%"]);
        }

        // 7. Salary filter
        $salaryMin = isset($filters['salary_min']) ? (int) $filters['salary_min'] : null;
        $salaryMax = isset($filters['salary_max']) ? (int) $filters['salary_max'] : null;
        $query = $this->applySalaryFilter($query, $salaryMin, $salaryMax);

        // 8. Experience filter
        $expMin = isset($filters['exp_min']) ? (int) $filters['exp_min'] : null;
        $expMax = isset($filters['exp_max']) ? (int) $filters['exp_max'] : null;
        if ($expMin !== null || $expMax !== null) {
            $query = $this->applyExperienceFilter($query, $expMin, $expMax);
        }

        // 9. Job level filter
        if (!empty($filters['job_level'])) {
            $query->where('listings.job_level', strtolower($filters['job_level']));
        }

        // 10. Company size filter (join to users table)
        if (!empty($filters['company_size'])) {
            $query->whereHas('employer', function (Builder $q) use ($filters) {
                $q->where('company_size', $filters['company_size']);
            });
        }

        // 11. Sort
        $sort  = $filters['sort'] ?? ($hasKeyword ? 'relevance' : 'newest');
        $query = $this->applySort($query, $sort, $hasKeyword);

        // 12. Pagination
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        if ($perPage < 5 || $perPage > 50) {
            $perPage = 15;
        }

        // Remove 'page' from appends so Laravel uses its own page detection
        $appendFilters = array_filter(
            $filters,
            fn ($key) => $key !== 'page',
            ARRAY_FILTER_USE_KEY
        );

        return $query->paginate($perPage)->appends($appendFilters);
    }

    /**
     * Trả về toàn bộ danh sách kỹ năng (không phân trang), sắp xếp theo tên.
     *
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return Skill::orderBy('name')->get();
    }

    /**
     * Trả về danh sách địa chỉ distinct từ Active_Listing, sắp xếp alphabet.
     *
     * @return array<string>
     */
    public function getCities(): array
    {
        return Listing::active()
            ->distinct()
            ->orderBy('address')
            ->pluck('address')
            ->toArray();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Áp dụng FULLTEXT search theo keyword.
     * Thêm MATCH(title, predes) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score
     * và WHERE relevance_score > 0.
     *
     * On SQLite (test environment), falls back to LIKE-based search since SQLite
     * does not support FULLTEXT indexes. relevance_score is set to a constant 1.0.
     */
    private function applyKeywordFilter(Builder $query, string $keyword): Builder
    {
        if (config('database.default') === 'sqlite') {
            // SQLite fallback: LIKE search on title + predes, constant relevance_score
            $escaped = '%' . $keyword . '%';
            return $query
                ->selectRaw('listings.*, 1.05 AS relevance_score')
                ->where(function (Builder $q) use ($keyword) {
                    $q->where('listings.title', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('listings.predes', 'LIKE', '%' . $keyword . '%');
                });
        }
        return $query
            ->selectRaw(
                'listings.*, MATCH(title, predes) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score',
                [$keyword]
            )
            ->whereRaw(
                'MATCH(title, predes) AGAINST(? IN NATURAL LANGUAGE MODE) > 0',
                [$keyword]
            );
    }

    /**
     * Áp dụng bộ lọc kỹ năng (AND / OR mode).
     * AND mode: subquery GROUP BY listing_id HAVING COUNT(DISTINCT skill_id) = N
     * OR mode: whereHas với whereIn
     */
    private function applySkillFilter(Builder $query, array $skillIds, string $mode): Builder
    {
        // Validate: chỉ giữ skill_id thực sự tồn tại trong bảng skills
        $validIds = Skill::whereIn('id', $skillIds)->pluck('id')->toArray();

        if (empty($validIds)) {
            // Không có ID hợp lệ → không áp dụng filter
            return $query;
        }

        if ($mode === 'or') {
            return $query->whereHas('skills', function (Builder $q) use ($validIds) {
                $q->whereIn('skills.id', $validIds);
            });
        }

        // AND mode: subquery HAVING COUNT = N
        $count       = count($validIds);
        $placeholders = implode(',', array_fill(0, $count, '?'));

        return $query->whereIn('listings.id', function ($sub) use ($validIds, $count, $placeholders) {
            $sub->selectRaw('listing_id')
                ->from('listing_skill')
                ->whereRaw("skill_id IN ({$placeholders})", $validIds)
                ->groupBy('listing_id')
                ->havingRaw('COUNT(DISTINCT skill_id) = ?', [$count]);
        });
    }

    /**
     * Áp dụng bộ lọc kinh nghiệm (intersection logic).
     * (experience_years_max >= expMin OR experience_years_max IS NULL)
     * AND (experience_years_min <= expMax OR experience_years_min IS NULL)
     */
    private function applyExperienceFilter(Builder $query, ?int $expMin, ?int $expMax): Builder
    {
        if ($expMin !== null) {
            $query->where(function (Builder $q) use ($expMin) {
                $q->whereNull('experience_years_max')
                  ->orWhere('experience_years_max', '>=', $expMin);
            });
        }

        if ($expMax !== null) {
            $query->where(function (Builder $q) use ($expMax) {
                $q->whereNull('experience_years_min')
                  ->orWhere('experience_years_min', '<=', $expMax);
            });
        }

        return $query;
    }

    /**
     * Áp dụng bộ lọc mức lương.
     * Chỉ kích hoạt khi salary_min > 0 HOẶC salary_max > 0.
     * Luôn thêm AND salary > 0 khi filter được kích hoạt.
     */
    private function applySalaryFilter(Builder $query, ?int $salaryMin, ?int $salaryMax): Builder
    {
        $minActive = $salaryMin !== null && $salaryMin > 0;
        $maxActive = $salaryMax !== null && $salaryMax > 0;

        if (!$minActive && !$maxActive) {
            // salary_min=0 (hoặc null) và salary_max=0 (hoặc null) → không filter
            return $query;
        }

        // Loại trừ "thỏa thuận" (salary=0) khi filter lương được kích hoạt
        $query->where('salary', '>', 0);

        if ($minActive) {
            $query->where('salary', '>=', $salaryMin);
        }

        if ($maxActive) {
            $query->where('salary', '<=', $salaryMax);
        }

        return $query;
    }

    /**
     * Áp dụng sắp xếp kết quả.
     * Hỗ trợ: relevance, newest, salary_desc, salary_asc, closing_soon.
     * Fallback về newest khi sort=relevance nhưng không có keyword.
     */
    private function applySort(Builder $query, string $sort, bool $hasKeyword): Builder
    {
        // Fallback: relevance yêu cầu keyword
        if ($sort === 'relevance' && !$hasKeyword) {
            $sort = 'newest';
        }

        return match ($sort) {
            'relevance'    => $query->orderByDesc('relevance_score'),
            'salary_desc'  => $query->orderByRaw('CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary DESC'),
            'salary_asc'   => $query->orderByRaw('CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary ASC'),
            'closing_soon' => $query->orderBy('application_close_date'),
            default        => $query->orderByDesc('created_at'), // 'newest' and any invalid sort
        };
    }

    /**
     * Escape ký tự đặc biệt trong LIKE pattern (%, _, \).
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
