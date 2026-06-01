# Design Document

## Overview

Module **Job Search Filter** cung cấp 3 API endpoints:
- `GET /api/listings/search` — tìm kiếm, lọc, phân trang tin tuyển dụng
- `GET /api/skills` — danh sách kỹ năng
- `GET /api/listings/cities` — danh sách địa chỉ distinct

Hệ thống tận dụng **MySQL 8.0 FULLTEXT INDEX** (`ft_listings_title_desc`) để tìm kiếm theo từ khóa với relevance scoring, kết hợp với các bộ lọc đa chiều (job_type, work_mode, skills AND/OR, address/city, salary range, experience intersection, job_level, company_size). Toàn bộ logic được đóng gói trong một Service class, Controller chỉ đóng vai trò điều phối, và API Resource đảm bảo response shape nhất quán.

### Quyết định thiết kế chính

| Quyết định | Lựa chọn | Lý do |
|---|---|---|
| Kiến trúc | Service Layer Pattern | Tách biệt business logic khỏi HTTP layer, dễ test |
| Routes | `routes/api.php` — prefix `/api` | Nhất quán với Req 16: `GET /api/listings/search` |
| FULLTEXT search | MySQL MATCH...AGAINST (natural language mode) | Tận dụng index sẵn có, không cần thư viện ngoài |
| Skill filter (AND) | Validate skill_id tồn tại trong DB → subquery GROUP BY + HAVING COUNT | Lọc invalid IDs trước, tránh HAVING COUNT sai |
| Skill filter (OR) | `whereHas` với `whereIn` | Đơn giản, đủ hiệu năng cho OR logic |
| Salary filter | Chỉ kích hoạt khi `salary_min > 0` hoặc `salary_max > 0` | `salary_min=0` là giá trị mặc định, không phải filter |
| Salary=0 | Loại khỏi kết quả khi filter lương được kích hoạt | Salary=0 là "thỏa thuận", không thể so sánh số |
| Experience filter | Intersection logic: khoảng tin chồng lấp khoảng user | Tránh loại bỏ tin phù hợp do logic quá chặt |
| Cities endpoint | Trả về `address` thô (raw), không parse | Nhất quán với Req 16 AC3; client tự xử lý hiển thị |
| Page không hợp lệ | Fallback về page=1 (không trả 422) | Req 15 AC11: dùng default, không báo lỗi |
| Timezone | `CURDATE()` với DB server UTC+7 | Req 12 AC2: dùng CURDATE() không dùng NOW() |
| Pagination | Laravel `paginate()` với `appends()` | Tự động tạo next/prev URL với query string |


---

## Architecture

### Tổng quan luồng xử lý

```mermaid
flowchart TD
    Client([Client / Browser]) -->|GET /api/listings/search?keyword=...| Route[routes/api.php]
    Route --> Controller[ListingController@index]
    Controller --> FormRequest[SearchFilterRequest\nValidation & Sanitization]
    FormRequest -->|validated params| Controller
    Controller --> Service[ListingSearchService@search]
    Service --> QueryBuilder[Eloquent Query Builder]
    QueryBuilder -->|FULLTEXT + filters + joins| DB[(MySQL 8.0\nlistings / users / skills)]
    DB --> QueryBuilder
    QueryBuilder --> Service
    Service -->|LengthAwarePaginator| Controller
    Controller --> Resource[ListingResource::collection]
    Resource -->|JSON response| Client
```

### Các thành phần và file

```
laravel_app/
├── routes/
│   └── api.php                              ← Routes: /api/listings/search, /api/skills, /api/listings/cities
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ListingController.php        ← index(), skills(), cities()
│   │   ├── Requests/
│   │   │   └── SearchFilterRequest.php      ← Validation + sanitization
│   │   └── Resources/
│   │       ├── ListingResource.php          ← Định dạng JSON cho mỗi Listing
│   │       └── SkillResource.php            ← Định dạng JSON cho mỗi Skill
│   ├── Services/
│   │   └── ListingSearchService.php         ← Toàn bộ query logic
│   └── Models/
│       ├── Listing.php                      ← Model + relationships
│       └── Skill.php                        ← Model skill
├── database/
│   └── migrations/
│       └── xxxx_add_search_columns_to_listings_table.php  ← work_mode, exp_min/max, job_level
```


### 4. `SkillResource`

**Namespace:** `App\Http\Resources\SkillResource`
**Extends:** `Illuminate\Http\Resources\Json\JsonResource`

```php
class SkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
```

### 5. `ListingController`

**Namespace:** `App\Http\Controllers\ListingController`

```php
class ListingController extends Controller
{
    public function __construct(private ListingSearchService $service) {}

    // GET /api/listings/search
    public function index(SearchFilterRequest $request): JsonResponse
    {
        $results = $this->service->search($request->validated());
        return ListingResource::collection($results)->response();
    }

    // GET /api/skills
    public function skills(): JsonResponse
    {
        return SkillResource::collection($this->service->getSkills())->response();
    }

    // GET /api/listings/cities
    public function cities(): JsonResponse
    {
        return response()->json($this->service->getCities());
    }
}
```

**Routes (`routes/api.php`):**
```php
Route::get('/listings/search', [ListingController::class, 'index']);
Route::get('/listings/cities', [ListingController::class, 'cities']);
Route::get('/skills',          [ListingController::class, 'skills']);
```

## Components and Interfaces

### 1. `SearchFilterRequest`
**Extends:** `Illuminate\Foundation\Http\FormRequest`

Form Request chịu trách nhiệm validate và sanitize toàn bộ 17 tham số đầu vào. Trả về HTTP 422 tự động khi validation thất bại.

```php
class SearchFilterRequest extends FormRequest
{
    public function authorize(): bool;

    public function rules(): array;
    // Trả về rules cho 17 params:
    // 'keyword'      => ['nullable', 'string', 'max:255']
    // 'job_type'     => ['nullable', 'string', 'in:full-time,part-time,freelance,internship']
    // 'work_mode'    => ['nullable', 'string', 'in:onsite,remote,hybrid']
    // 'skills'       => ['nullable', 'array', 'max:15']
    // 'skills.*'     => ['integer', 'min:1']
    // 'skill_mode'   => ['nullable', 'string', 'in:and,or']
    // 'address'      => ['nullable', 'string', 'max:255']
    // 'city'         => ['nullable', 'string', 'max:255']
    // 'salary_min'   => ['nullable', 'integer', 'min:0', 'max:999999999']
    // 'salary_max'   => ['nullable', 'integer', 'min:0', 'max:999999999', 'gte:salary_min']
    // 'exp_min'      => ['nullable', 'integer', 'min:0', 'max:99']
    // 'exp_max'      => ['nullable', 'integer', 'min:0', 'max:99', 'gte:exp_min']
    // 'job_level'    => ['nullable', 'string', 'in:intern,fresher,junior,middle,senior,lead,manager']
    // 'company_size' => ['nullable', 'string', 'in:1-9,10-49,50-199,200-499,500+']
    // 'sort'         => ['nullable', 'string', 'in:relevance,newest,salary_desc,salary_asc,closing_soon']
    // 'page'         => ['nullable', 'integer', 'min:1']
    // 'per_page'     => ['nullable', 'integer', 'min:5', 'max:50']

    public function messages(): array;
    // Trả về error messages tiếng Việt cho từng rule

    protected function prepareForValidation(): void;
    // Sanitize: trim keyword, cắt keyword/address/city xuống 255 ký tự nếu vượt quá
    // Normalize: skill_mode default 'and', per_page default 15, page default 1
}
```

### 2. `ListingSearchService`

**Namespace:** `App\Services\ListingSearchService`

Service class chứa toàn bộ query logic. Không phụ thuộc trực tiếp vào HTTP layer, dễ unit test bằng cách mock Eloquent.

```php
class ListingSearchService
{
    /**
     * Tìm kiếm và lọc tin tuyển dụng.
     *
     * @param  array $filters  Mảng đã validated từ SearchFilterRequest::validated()
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters): LengthAwarePaginator;

    /**
     * Trả về toàn bộ danh sách kỹ năng (không phân trang).
     *
     * @return \Illuminate\Support\Collection<Skill>
     */
    public function getSkills(): Collection;

    /**
     * Trả về danh sách thành phố distinct từ Active_Listing, sắp xếp alphabet.
     *
     * @return array<string>
     */
    public function getCities(): array;

    // --- Private helpers ---

    private function applyKeywordFilter(Builder $query, string $keyword): Builder;
    // Thêm MATCH(title, predes) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score
    // và WHERE relevance_score > 0

    private function applySkillFilter(Builder $query, array $skillIds, string $mode): Builder;
    // Bước 1: Lọc chỉ giữ skill_id tồn tại trong bảng skills (tránh HAVING COUNT sai)
    //   $validIds = Skill::whereIn('id', $skillIds)->pluck('id')->toArray();
    //   if (empty($validIds)) return $query; // không áp dụng filter nếu không có ID hợp lệ
    // AND mode: subquery với GROUP BY listing_id HAVING COUNT(DISTINCT skill_id) = count($validIds)
    // OR mode: whereHas('skills', fn($q) => $q->whereIn('skills.id', $validIds))

    private function applyExperienceFilter(Builder $query, ?int $expMin, ?int $expMax): Builder;
    // Intersection logic: (exp_max >= expMin OR exp_max IS NULL) AND (exp_min <= expMax OR exp_min IS NULL)

    private function applySalaryFilter(Builder $query, ?int $salaryMin, ?int $salaryMax): Builder;
    // Chỉ kích hoạt khi salary_min > 0 HOẶC salary_max > 0
    // salary_min = 0 (hoặc null) + salary_max = 0 (hoặc null) → không áp dụng filter
    // salary_min = 0 + salary_max > 0 → chỉ áp dụng: salary <= salary_max AND salary > 0
    // salary_min > 0 + salary_max = 0 (hoặc null) → chỉ áp dụng: salary >= salary_min AND salary > 0
    // salary_min > 0 + salary_max > 0 → áp dụng: salary >= salary_min AND salary <= salary_max AND salary > 0

    private function applySort(Builder $query, string $sort, bool $hasKeyword): Builder;
    // Xử lý 5 sort options, fallback về newest khi sort=relevance nhưng không có keyword
}
```

### 3. `ListingResource`

**Namespace:** `App\Http\Resources\ListingResource`
**Extends:** `Illuminate\Http\Resources\Json\JsonResource`

Định dạng JSON response cho mỗi Listing. Tất cả nullable fields phải trả về `null` (không phải `""`).

```php
class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'title'                   => $this->title,
            'slug'                    => $this->slug,
            'predes_truncated'        => $this->truncatePredes($this->predes, 200),
            // Cắt tại ranh giới từ, thêm "..." nếu bị cắt; null nếu predes là null

            'job_type'                => $this->job_type,
            'work_mode'               => $this->work_mode,
            'job_level'               => $this->job_level,           // nullable
            'address'                 => $this->address,
            'salary'                  => $this->salary,
            'salary_display'          => $this->formatSalary($this->salary),
            // salary=0 → "Thỏa thuận"; else → "15,000,000 VNĐ"

            'experience_years_min'    => $this->experience_years_min, // nullable
            'experience_years_max'    => $this->experience_years_max, // nullable
            'experience_display'      => $this->formatExperience(
                                             $this->experience_years_min,
                                             $this->experience_years_max
                                         ),
            // null/null → "Không yêu cầu kinh nghiệm"
            // min only → "Từ {min} năm"
            // max only → "Dưới {max} năm"
            // both     → "Từ {min} đến {max} năm"

            'application_close_date'  => $this->application_close_date,
            'created_at'              => $this->created_at,

            'employer' => [
                'company_name'  => $this->employer->company_name,
                'company_logo'  => $this->employer->company_logo,  // nullable
                'company_size'  => $this->employer->company_size,
            ],

            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            // Mỗi skill: { id, name, slug }; mảng rỗng [] nếu không có skill

            'relevance_score' => $this->when(
                isset($this->relevance_score),
                fn() => (float) $this->relevance_score
            ),
            // Chỉ xuất hiện trong response khi có keyword
        ];
    }

    private function truncatePredes(?string $text, int $maxLen): ?string;
    private function formatSalary(int $salary): string;
    private function formatExperience(?int $min, ?int $max): string;
}
```

---

## Data Models

### Migration mới cần tạo

```php
// database/migrations/xxxx_add_search_columns_to_listings_table.php
Schema::table('listings', function (Blueprint $table) {
    $table->enum('work_mode', ['onsite', 'remote', 'hybrid'])
          ->notNull()->default('onsite')->after('job_type');
    $table->tinyInteger('experience_years_min')->unsigned()->nullable()->after('work_mode');
    $table->tinyInteger('experience_years_max')->unsigned()->nullable()->after('experience_years_min');
    $table->enum('job_level', ['intern','fresher','junior','middle','senior','lead','manager'])
          ->nullable()->after('experience_years_max');

    $table->index('work_mode',            'idx_listings_work_mode');
    $table->index('experience_years_min', 'idx_listings_exp_min');
    $table->index('experience_years_max', 'idx_listings_exp_max');
    $table->index('job_level',            'idx_listings_job_level');
});

// Cập nhật ENUM job_type: loại bỏ 'remote' và 'hybrid' (chuyển sang work_mode)
// Thực hiện bằng raw SQL trong migration riêng:
// ALTER TABLE listings MODIFY job_type ENUM('full-time','part-time','freelance','internship') NOT NULL DEFAULT 'full-time';
```

### Listing Model

```php
class Listing extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'predes', 'description',
        'requirements', 'benefits', 'job_type', 'work_mode',
        'experience_years_min', 'experience_years_max', 'job_level',
        'address', 'salary', 'feature_image', 'application_close_date', 'status',
    ];

    protected $casts = [
        'requirements'         => 'array',
        'benefits'             => 'array',
        'application_close_date' => 'date',
        'salary'               => 'integer',
        'experience_years_min' => 'integer',
        'experience_years_max' => 'integer',
    ];

    // Relationships
    public function employer(): BelongsTo  // → users (user_type = 'employer')
    public function skills(): BelongsToMany  // → skills via listing_skill

    // Scope: chỉ lấy Active_Listing
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'open')
                     ->whereDate('application_close_date', '>=', DB::raw('CURDATE()'));
    }
}
```

### Query pattern cho FULLTEXT + filters

```sql
-- Ví dụ query đầy đủ khi có keyword + skill AND filter
SELECT
    l.*,
    MATCH(l.title, l.predes) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance_score
FROM listings l
INNER JOIN users u ON u.id = l.user_id
WHERE l.status = 'open'
  AND l.application_close_date >= CURDATE()
  AND MATCH(l.title, l.predes) AGAINST(? IN NATURAL LANGUAGE MODE) > 0
  AND l.id IN (
      SELECT listing_id FROM listing_skill
      WHERE skill_id IN (?, ?, ?)   -- valid skill IDs only
      GROUP BY listing_id
      HAVING COUNT(DISTINCT skill_id) = 3  -- AND mode
  )
ORDER BY relevance_score DESC
LIMIT 15 OFFSET 0;
```

---

## Correctness Properties

Các property dùng cho Property-Based Testing (PBT) để xác minh tính đúng đắn của Search_Engine:

**P1 — Active-only invariant:**
Với mọi Search_Request hợp lệ, mọi Listing trong Result_Set phải có `status = 'open'` VÀ `application_close_date >= CURDATE()`. Không có ngoại lệ.

**P2 — Relevance score positive:**
Với mọi Search_Request có Keyword không rỗng, mọi Listing trong Result_Set phải có `relevance_score > 0`. Không có Listing nào với `relevance_score = 0` hoặc thiếu trường này.

**P3 — Salary filter excludes negotiable:**
Với mọi Search_Request có `salary_min > 0` hoặc `salary_max > 0`, không có Listing nào trong Result_Set có `salary = 0`.

**P4 — Experience intersection:**
Với mọi Search_Request có `exp_min = A` và `exp_max = B` (A ≤ B), mọi Listing trong Result_Set phải thỏa mãn: `(experience_years_max >= A OR experience_years_max IS NULL) AND (experience_years_min <= B OR experience_years_min IS NULL)`.

**P5 — Pagination consistency:**
Với mọi Search_Request, tổng số phần tử khi lấy tất cả trang phải bằng `total` trong metadata phân trang. Cụ thể: `SUM(count(data) for page in 1..last_page) == total`.

**P6 — Skill AND completeness:**
Với mọi Search_Request có `skills = [s1, s2, ..., sN]` và `skill_mode = 'and'`, mọi Listing trong Result_Set phải có liên kết với tất cả N skill IDs hợp lệ trong bảng `listing_skill`.

---

## Error Handling

### HTTP 422 — Validation Error

Trả về khi `salary_min > salary_max` hoặc `exp_min > exp_max`:

```json
{
    "message": "Dữ liệu không hợp lệ.",
    "errors": {
        "salary_min": ["Mức lương tối thiểu không được lớn hơn mức lương tối đa."],
        "exp_min":    ["Kinh nghiệm tối thiểu không được lớn hơn kinh nghiệm tối đa."]
    }
}
```

### HTTP 405 — Method Not Allowed

Trả về tự động bởi Laravel khi dùng method khác GET cho 3 endpoints.

### Fallback behaviors (không trả lỗi)

| Tình huống | Hành vi |
|---|---|
| `page` không hợp lệ | Fallback về page = 1 |
| `per_page` ngoài [5, 50] | Fallback về per_page = 15 |
| `job_type` / `work_mode` / `job_level` / `company_size` không hợp lệ | Bỏ qua filter, không báo lỗi |
| `skill_mode` không hợp lệ | Fallback về `and` |
| `skills[]` chứa ID không tồn tại | Bỏ qua ID đó |
| `skills[]` > 15 phần tử | Chỉ dùng 15 phần tử đầu |
| `keyword` > 255 ký tự | Cắt xuống 255 ký tự |
| `address` / `city` > 255 ký tự | Cắt xuống 255 ký tự |
| Trang yêu cầu > last_page | Trả về data rỗng, pagination metadata đầy đủ |
| `total = 0`, page > 1 | Trả về data rỗng, `last_page = 1`, cả hai URL = null |

---

## Testing Strategy

### Unit Tests — `ListingSearchServiceTest`

Test từng private method của `ListingSearchService` bằng cách inject mock Eloquent Builder:

- `applyKeywordFilter`: kiểm tra FULLTEXT query được thêm đúng khi có keyword, không thêm khi keyword rỗng
- `applySkillFilter` AND mode: kiểm tra subquery HAVING COUNT = N, bỏ qua invalid skill IDs
- `applySkillFilter` OR mode: kiểm tra whereHas được gọi đúng
- `applySalaryFilter`: kiểm tra 4 cases (no filter, min only, max only, both); đặc biệt `salary_min=0` không kích hoạt filter
- `applyExperienceFilter`: kiểm tra intersection logic với NULL handling
- `applySort`: kiểm tra 5 sort options, fallback relevance→newest khi không có keyword
- `getCities`: kiểm tra trả về mảng string, sắp xếp alphabet

### Feature Tests — `ListingSearchApiTest`

Test HTTP endpoints end-to-end với database seeding:

```php
// Ví dụ test cases
it('returns only open listings not past deadline');
it('returns relevance_score only when keyword provided');
it('excludes salary=0 listings when salary_min > 0');
it('applies experience intersection logic correctly');
it('returns 422 when salary_min > salary_max');
it('falls back to page 1 when page param is invalid');
it('limits skills array to 15 elements');
it('GET /api/skills returns all skills without pagination');
it('GET /api/listings/cities returns distinct addresses sorted alphabetically');
it('returns 405 for POST /api/listings/search');
```

### Property-Based Tests

Sử dụng [eris/eris](https://github.com/giorgiosironi/eris) hoặc viết generator thủ công để verify P1–P6:

```php
// P1: Với mọi bộ filter ngẫu nhiên hợp lệ, kết quả luôn chỉ chứa Active_Listing
it('P1: all results are active listings for any valid filter combination', function () {
    // Generate random valid filters, call search(), assert all results active
});

// P5: Pagination total nhất quán
it('P5: sum of all pages equals total', function () {
    // Fetch all pages, sum data counts, compare with total
});
```
