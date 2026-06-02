# Implementation Plan: Job Search Filter

## Overview

Triển khai module tìm kiếm và lọc tin tuyển dụng IT theo kiến trúc Service Layer Pattern trên Laravel 11 + MySQL 8.0. Luồng thực hiện: DB Migration → Models → Service Layer → HTTP Layer (Request/Resource/Controller) → Routes → Tests.

## Tasks

- [x] 1. Tạo DB Migration cho bảng `listings`
  - [x] 1.1 Tạo migration thêm các cột mới vào bảng `listings`
    - Tạo file `database/migrations/xxxx_add_search_columns_to_listings_table.php`
    - Thêm cột `work_mode` ENUM('onsite','remote','hybrid') NOT NULL DEFAULT 'onsite' sau cột `job_type`
    - Thêm cột `experience_years_min` TINYINT UNSIGNED NULL sau `work_mode`
    - Thêm cột `experience_years_max` TINYINT UNSIGNED NULL sau `experience_years_min`
    - Thêm cột `job_level` ENUM('intern','fresher','junior','middle','senior','lead','manager') NULL sau `experience_years_max`
    - Thêm các index: `idx_listings_work_mode`, `idx_listings_exp_min`, `idx_listings_exp_max`, `idx_listings_job_level`
    - Implement `down()` để rollback (dropColumn + dropIndex)
    - _Requirements: 3.1, 7.1, 8.1, 14.4_

  - [x] 1.2 Tạo migration cập nhật ENUM `job_type`
    - Tạo file migration riêng để thực thi raw SQL:
      `ALTER TABLE listings MODIFY job_type ENUM('full-time','part-time','freelance','internship') NOT NULL DEFAULT 'full-time';`
    - Implement `down()` để khôi phục ENUM cũ (thêm lại 'remote','hybrid')
    - _Requirements: 2.2_

- [x] 2. Cập nhật và tạo Models
  - [x] 2.1 Tạo/cập nhật `Listing` model
    - Tạo file `app/Models/Listing.php` (nếu chưa có)
    - Khai báo `$fillable` bao gồm tất cả cột cần thiết: `user_id`, `title`, `slug`, `predes`, `description`, `requirements`, `benefits`, `job_type`, `work_mode`, `experience_years_min`, `experience_years_max`, `job_level`, `address`, `salary`, `feature_image`, `application_close_date`, `status`
    - Khai báo `$casts`: `requirements` → `array`, `benefits` → `array`, `application_close_date` → `date`, `salary` → `integer`, `experience_years_min` → `integer`, `experience_years_max` → `integer`
    - Implement relationship `employer()`: `BelongsTo` → `User` (via `user_id`)
    - Implement relationship `skills()`: `BelongsToMany` → `Skill` via bảng pivot `listing_skill`
    - Implement scope `scopeActive(Builder $query)`: lọc `status = 'open'` AND `application_close_date >= CURDATE()`
    - _Requirements: 12.1, 12.2, 13.2, 13.3, 14.2_

  - [x] 2.2 Tạo `Skill` model
    - Tạo file `app/Models/Skill.php` (nếu chưa có)
    - Khai báo `$fillable`: `name`, `slug`
    - Implement relationship `listings()`: `BelongsToMany` → `Listing` via `listing_skill`
    - _Requirements: 4.7, 16.2_

- [ ] 3. Tạo Service Layer
  - [ ] 3.1 Tạo `ListingSearchService` — skeleton và `getSkills()` / `getCities()`
    - Tạo thư mục `app/Services/` và file `app/Services/ListingSearchService.php`
    - Implement `getSkills()`: trả về `Skill::all()` dưới dạng Collection
    - Implement `getCities()`: trả về mảng string — `address` distinct từ Active_Listing, sắp xếp alphabet (`Listing::active()->distinct()->orderBy('address')->pluck('address')->toArray()`)
    - _Requirements: 4.7, 5.4, 16.2, 16.3_

  - [ ] 3.2 Implement `applyKeywordFilter` trong `ListingSearchService`
    - Thêm private method `applyKeywordFilter(Builder $query, string $keyword): Builder`
    - Dùng `selectRaw('listings.*, MATCH(title, predes) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score', [$keyword])`
    - Thêm `whereRaw('MATCH(title, predes) AGAINST(? IN NATURAL LANGUAGE MODE) > 0', [$keyword])`
    - _Requirements: 1.1, 1.2, 1.5, 14.1_

  - [ ] 3.3 Implement `applySkillFilter` trong `ListingSearchService`
    - Thêm private method `applySkillFilter(Builder $query, array $skillIds, string $mode): Builder`
    - Bước 1: validate IDs — `$validIds = Skill::whereIn('id', $skillIds)->pluck('id')->toArray()`; nếu rỗng thì return `$query` không đổi
    - AND mode: dùng `whereIn` với subquery `SELECT listing_id FROM listing_skill WHERE skill_id IN (...) GROUP BY listing_id HAVING COUNT(DISTINCT skill_id) = N`
    - OR mode: dùng `whereHas('skills', fn($q) => $q->whereIn('skills.id', $validIds))`
    - _Requirements: 4.1, 4.2, 4.4, 4.5_

  - [ ] 3.4 Implement `applyExperienceFilter` và `applySalaryFilter` trong `ListingSearchService`
    - Thêm private method `applyExperienceFilter(Builder $query, ?int $expMin, ?int $expMax): Builder`
      - Intersection logic: nếu `$expMin` không null → `WHERE (experience_years_max >= $expMin OR experience_years_max IS NULL)`
      - Nếu `$expMax` không null → `WHERE (experience_years_min <= $expMax OR experience_years_min IS NULL)`
    - Thêm private method `applySalaryFilter(Builder $query, ?int $salaryMin, ?int $salaryMax): Builder`
      - Chỉ kích hoạt khi `$salaryMin > 0` hoặc `$salaryMax > 0`
      - Luôn thêm `AND salary > 0` khi filter được kích hoạt
      - Áp dụng `salary >= $salaryMin` nếu `$salaryMin > 0`
      - Áp dụng `salary <= $salaryMax` nếu `$salaryMax > 0`
    - _Requirements: 6.1, 6.2, 6.3, 6.5, 7.1, 7.2, 7.3, 7.5_

  - [ ] 3.5 Implement `applySort` và method `search()` chính trong `ListingSearchService`
    - Thêm private method `applySort(Builder $query, string $sort, bool $hasKeyword): Builder`
      - `relevance`: `orderByDesc('relevance_score')` — fallback về `newest` nếu `!$hasKeyword`
      - `newest`: `orderByDesc('created_at')`
      - `salary_desc`: `orderByRaw('CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary DESC')`
      - `salary_asc`: `orderByRaw('CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary ASC')`
      - `closing_soon`: `orderBy('application_close_date')`
    - Implement `search(array $filters): LengthAwarePaginator`
      - Khởi tạo query: `Listing::active()->with(['employer', 'skills'])->select('listings.*')`
      - Gọi lần lượt các private helpers dựa trên filters
      - Xử lý default sort: có keyword → `relevance`, không có → `newest`
      - Xử lý `per_page`: nếu ngoài [5,50] hoặc null → dùng 15
      - Trả về `$query->paginate($perPage)->appends($filters)`
    - _Requirements: 1.3, 1.4, 10.1, 10.2, 10.3, 10.4, 11.1, 11.2, 11.3, 11.4, 11.5, 14.2, 14.3_

- [ ] 4. Tạo HTTP Layer
  - [ ] 4.1 Tạo `SearchFilterRequest`
    - Tạo file `app/Http/Requests/SearchFilterRequest.php`
    - Implement `authorize()`: trả về `true`
    - Implement `rules()` với đầy đủ 17 params:
      - `keyword`: `['nullable', 'string', 'max:255']`
      - `job_type`: `['nullable', 'string', 'in:full-time,part-time,freelance,internship']`
      - `work_mode`: `['nullable', 'string', 'in:onsite,remote,hybrid']`
      - `skills`: `['nullable', 'array', 'max:15']`; `skills.*`: `['integer', 'min:1']`
      - `skill_mode`: `['nullable', 'string', 'in:and,or']`
      - `address`, `city`: `['nullable', 'string', 'max:255']`
      - `salary_min`: `['nullable', 'integer', 'min:0', 'max:999999999']`
      - `salary_max`: `['nullable', 'integer', 'min:0', 'max:999999999', 'gte:salary_min']`
      - `exp_min`: `['nullable', 'integer', 'min:0', 'max:99']`
      - `exp_max`: `['nullable', 'integer', 'min:0', 'max:99', 'gte:exp_min']`
      - `job_level`: `['nullable', 'string', 'in:intern,fresher,junior,middle,senior,lead,manager']`
      - `company_size`: `['nullable', 'string', 'in:1-9,10-49,50-199,200-499,500+']`
      - `sort`: `['nullable', 'string', 'in:relevance,newest,salary_desc,salary_asc,closing_soon']`
      - `page`: `['nullable', 'integer', 'min:1']`
      - `per_page`: `['nullable', 'integer', 'min:5', 'max:50']`
    - Implement `messages()` với error messages tiếng Việt cho tất cả rules
    - Implement `prepareForValidation()`: trim keyword, cắt keyword/address/city xuống 255 ký tự, set default `skill_mode = 'and'`
    - _Requirements: 15.1–15.12, 6.4, 7.4_

  - [ ] 4.2 Tạo `SkillResource`
    - Tạo file `app/Http/Resources/SkillResource.php`
    - Implement `toArray()`: trả về `['id', 'name', 'slug']`
    - _Requirements: 4.7, 13.3, 16.2_

  - [ ] 4.3 Tạo `ListingResource`
    - Tạo file `app/Http/Resources/ListingResource.php`
    - Implement `toArray()` với đầy đủ các trường theo design: `id`, `title`, `slug`, `predes_truncated`, `job_type`, `work_mode`, `job_level`, `address`, `salary`, `salary_display`, `experience_years_min`, `experience_years_max`, `experience_display`, `application_close_date`, `created_at`
    - Implement nested `employer` object: `company_name`, `company_logo`, `company_size`
    - Implement `skills` collection dùng `SkillResource::collection($this->whenLoaded('skills'))`
    - Implement `relevance_score` với `$this->when(isset($this->relevance_score), fn() => (float) $this->relevance_score)`
    - Implement private `truncatePredes(?string $text, int $maxLen): ?string` — cắt tại ranh giới từ, thêm "..." nếu bị cắt, trả về null nếu input null
    - Implement private `formatSalary(int $salary): string` — `0` → "Thỏa thuận"; else → "15,000,000 VNĐ"
    - Implement private `formatExperience(?int $min, ?int $max): string` — 4 cases theo requirements
    - Đảm bảo tất cả nullable fields trả về `null` (không phải `""`)
    - _Requirements: 13.1–13.7_

  - [ ] 4.4 Tạo `ListingController`
    - Tạo file `app/Http/Controllers/ListingController.php`
    - Inject `ListingSearchService` qua constructor
    - Implement `index(SearchFilterRequest $request): JsonResponse` — gọi `$this->service->search($request->validated())`, trả về `ListingResource::collection($results)->response()`
    - Implement `skills(): JsonResponse` — gọi `$this->service->getSkills()`, trả về `SkillResource::collection(...)->response()`
    - Implement `cities(): JsonResponse` — gọi `$this->service->getCities()`, trả về `response()->json(...)`
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.7_

- [ ] 5. Đăng ký Routes
  - [ ] 5.1 Thêm 3 routes vào `routes/api.php`
    - Tạo file `routes/api.php` nếu chưa có (Laravel 11 cần đăng ký trong `bootstrap/app.php`)
    - Thêm: `Route::get('/listings/search', [ListingController::class, 'index'])`
    - Thêm: `Route::get('/listings/cities', [ListingController::class, 'cities'])`
    - Thêm: `Route::get('/skills', [ListingController::class, 'skills'])`
    - Đảm bảo import đúng namespace `App\Http\Controllers\ListingController`
    - _Requirements: 16.1, 16.2, 16.3, 16.6_

- [ ] 6. Checkpoint — Kiểm tra tích hợp cơ bản
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Viết Tests
  - [ ] 7.1 Viết Unit Tests cho `ListingSearchService`
    - Tạo file `tests/Unit/ListingSearchServiceTest.php`
    - Test `applyKeywordFilter`: FULLTEXT query được thêm khi có keyword; không thêm khi keyword rỗng
    - Test `applySkillFilter` AND mode: subquery HAVING COUNT = N; bỏ qua invalid skill IDs; trả về query không đổi khi tất cả IDs không hợp lệ
    - Test `applySkillFilter` OR mode: `whereHas` được gọi đúng
    - Test `applySalaryFilter`: 4 cases — no filter (min=0, max=0), min only (>0), max only (>0), cả hai; `salary_min=0` không kích hoạt filter
    - Test `applyExperienceFilter`: intersection logic với NULL handling — cả hai null, chỉ min, chỉ max, cả hai
    - Test `applySort`: 5 sort options; fallback `relevance` → `newest` khi không có keyword
    - Test `getCities`: trả về mảng string, sắp xếp alphabet
    - _Requirements: 1.1, 1.2, 4.1, 4.2, 4.4, 6.1, 6.2, 6.5, 7.1, 7.2, 11.1, 11.4_

  - [ ]* 7.2 Viết Property-Based Test P1 — Active-only invariant
    - **Property 1: Active-only invariant**
    - Với mọi bộ filter ngẫu nhiên hợp lệ, mọi Listing trong Result_Set phải có `status = 'open'` VÀ `application_close_date >= CURDATE()`
    - Dùng eris/eris hoặc generator thủ công để sinh random valid filters
    - **Validates: Requirements 12.1, 12.2**

  - [ ]* 7.3 Viết Property-Based Test P2 — Relevance score positive
    - **Property 2: Relevance score positive**
    - Với mọi Search_Request có Keyword không rỗng, mọi Listing trong Result_Set phải có `relevance_score > 0`
    - **Validates: Requirements 1.2, 13.4**

  - [ ]* 7.4 Viết Property-Based Test P3 — Salary filter excludes negotiable
    - **Property 3: Salary filter excludes negotiable**
    - Với mọi Search_Request có `salary_min > 0` hoặc `salary_max > 0`, không có Listing nào trong Result_Set có `salary = 0`
    - **Validates: Requirements 6.1, 6.2, 6.5**

  - [ ]* 7.5 Viết Property-Based Test P4 — Experience intersection
    - **Property 4: Experience intersection**
    - Với mọi Search_Request có `exp_min = A` và `exp_max = B` (A ≤ B), mọi Listing trong Result_Set phải thỏa mãn: `(experience_years_max >= A OR experience_years_max IS NULL) AND (experience_years_min <= B OR experience_years_min IS NULL)`
    - **Validates: Requirements 7.1, 7.2, 7.3**

  - [ ]* 7.6 Viết Property-Based Test P5 — Pagination consistency
    - **Property 5: Pagination consistency**
    - Với mọi Search_Request, tổng số phần tử khi lấy tất cả trang phải bằng `total` trong metadata phân trang: `SUM(count(data) for page in 1..last_page) == total`
    - **Validates: Requirements 10.1, 10.4, 10.5**

  - [ ]* 7.7 Viết Property-Based Test P6 — Skill AND completeness
    - **Property 6: Skill AND completeness**
    - Với mọi Search_Request có `skills = [s1, ..., sN]` và `skill_mode = 'and'`, mọi Listing trong Result_Set phải có liên kết với tất cả N skill IDs hợp lệ trong bảng `listing_skill`
    - **Validates: Requirements 4.1, 4.4**

  - [ ] 7.8 Viết Feature Tests cho 3 API endpoints
    - Tạo file `tests/Feature/ListingSearchApiTest.php`
    - Test `GET /api/listings/search`:
      - Chỉ trả về listing có `status = 'open'` và `application_close_date >= today`
      - Trả về `relevance_score` khi có keyword, không trả về khi không có keyword
      - Loại trừ `salary = 0` khi `salary_min > 0`
      - Áp dụng experience intersection logic đúng
      - Trả về HTTP 422 khi `salary_min > salary_max`
      - Trả về HTTP 422 khi `exp_min > exp_max`
      - Fallback về page 1 khi `page` không hợp lệ
      - Giới hạn `skills` array xuống 15 phần tử
      - Trả về HTTP 405 cho `POST /api/listings/search`
      - Trả về `Content-Type: application/json`
    - Test `GET /api/skills`: trả về tất cả skills không phân trang, format `{id, name, slug}`
    - Test `GET /api/listings/cities`: trả về distinct addresses sắp xếp alphabet, HTTP 405 cho POST
    - _Requirements: 16.1–16.7, 10.5, 10.6_

- [ ] 8. Final Checkpoint — Đảm bảo tất cả tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks đánh dấu `*` là optional, có thể bỏ qua để triển khai MVP nhanh hơn
- Mỗi task tham chiếu đến requirements cụ thể để đảm bảo traceability
- Migration 1.1 và 1.2 nên chạy theo thứ tự; 1.2 phụ thuộc vào schema hiện tại của `job_type`
- `ListingSearchService` không phụ thuộc HTTP layer — có thể unit test độc lập
- Eris/eris cần được cài qua `composer require --dev giorgiosironi/eris` trước khi viết PBT
- Laravel 11 không có `routes/api.php` mặc định — cần đăng ký trong `bootstrap/app.php` với `->withRouting(api: __DIR__.'/../routes/api.php')`

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["2.1", "2.2"] },
    { "id": 2, "tasks": ["3.1"] },
    { "id": 3, "tasks": ["3.2", "3.3", "3.4"] },
    { "id": 4, "tasks": ["3.5"] },
    { "id": 5, "tasks": ["4.1", "4.2"] },
    { "id": 6, "tasks": ["4.3"] },
    { "id": 7, "tasks": ["4.4"] },
    { "id": 8, "tasks": ["5.1"] },
    { "id": 9, "tasks": ["7.1", "7.8"] },
    { "id": 10, "tasks": ["7.2", "7.3", "7.4", "7.5", "7.6", "7.7"] }
  ]
}
```
