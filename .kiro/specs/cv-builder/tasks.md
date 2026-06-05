# Implementation Plan: CV Builder

## Overview

Triển khai module CV Builder trên Laravel 11 + Blade, bao gồm hai luồng chính: upload CV file và tạo CV online với DomPDF. Các task được chia theo nhóm logic, mỗi task xây dựng trên kết quả của task trước.

---

## Tasks

### 1. Database & Models

- [ ] 1.1 Tạo migration `create_cv_data_table`
  - Tạo file migration tại `database/migrations/`
  - Định nghĩa bảng `cv_data` với các cột: `id`, `user_id` (FK unique, cascadeOnDelete), `full_name` (string 255), `phone` (string 20 nullable), `email` (string 255 nullable), `address` (string 500 nullable), `objective` (text nullable), `education` (json nullable), `experience` (json nullable), `projects` (json nullable), `certifications` (json nullable), `skills_text` (text nullable), `languages` (json nullable), `photo_path` (string nullable), `template` (string 50 default 'default'), `timestamps`
  - Đảm bảo unique constraint trên `user_id`
  - _Requirements: 4.5_

- [ ] 1.2 Tạo Model `CvData`
  - Tạo file `app/Models/CvData.php`
  - Khai báo `$table = 'cv_data'`, `$fillable` đầy đủ tất cả các cột
  - Khai báo `$casts` cho 5 JSON columns: `education`, `experience`, `projects`, `certifications`, `languages` → `'array'`
  - Thêm quan hệ `user(): BelongsTo` trỏ về `User::class`
  - _Requirements: 4.1, 4.2, 4.4, 4.5_

- [ ] 1.3 Cập nhật Model `User` — thêm quan hệ `cvData`
  - Mở file `app/Models/User.php`
  - Thêm method `cvData(): HasOne` trỏ về `CvData::class`
  - _Requirements: 4.1, 4.5_

- [ ]* 1.4 Viết unit test cho `CvData` model
  - Cấu hình môi trường test dùng SQLite in-memory trong `phpunit.xml` bằng cách bỏ comment 2 dòng:
    ```xml
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    ```
    Điều này giúp unit test chạy nhanh mà không ảnh hưởng DB thật.
  - Tạo `tests/Unit/CvDataModelTest.php`
  - Test JSON casts: lưu array vào JSON column, đọc lại phải trả về array tương đương (**Property 9: JSON columns serialization round-trip**, **Validates: Requirements 4.2**)
  - Test upsert uniqueness: gọi `updateOrCreate` N lần với cùng `user_id`, verify `cv_data` count = 1 (**Property 10: One cv_data record per employee**, **Validates: Requirements 4.5**)
  - _Requirements: 4.2, 4.5_

---

### 2. Middleware & Form Requests

- [ ] 2.1 Tạo Middleware `EnsureEmployee`
  - Tạo file `app/Http/Middleware/EnsureEmployee.php`
  - Logic: nếu user đã auth và `user_type === 'employer'` → `abort(403, 'Chức năng này chỉ dành cho ứng viên.')`
  - Đăng ký alias `'employee'` trong `bootstrap/app.php` tại `$middleware->alias([...])`
  - **Không** đặt `abort(403)` trong bất kỳ method nào của controller — toàn bộ employer check tập trung tại middleware này
  - _Requirements: 8.3_

- [ ] 2.2 Tạo `UploadCvRequest`
  - Tạo file `app/Http/Requests/UploadCvRequest.php`
  - Khai báo `authorize()` trả về `true`
  - Định nghĩa rules: `cv_file` → `required|file|mimes:pdf,doc,docx|max:5120`
  - _Requirements: 1.3, 1.4_

- [ ] 2.3 Tạo `CvFormRequest`
  - Tạo file `app/Http/Requests/CvFormRequest.php`
  - Khai báo `authorize()` trả về `true`
  - Định nghĩa đầy đủ rules theo design — bao gồm sub-field validation cho các repeatable sections:
    - `full_name`: `required|string|max:255`
    - `phone`: `['sometimes', 'nullable', 'regex:/^[\d\s\+\-\(\)]{1,20}$/']`
      - Dùng `sometimes` thay vì chỉ `nullable` để tránh regex `{0,20}` match chuỗi rỗng
    - `email`: `nullable|email|max:255`
    - `address`: `nullable|string|max:500`
    - `objective`: `nullable|string|max:1000`
    - `education`: `nullable|array|max:10`
    - `education.*.title`: `nullable|string|max:150`
    - `education.*.subtitle`: `nullable|string|max:150`
    - `education.*.period`: `nullable|string|max:50`
    - `education.*.description`: `nullable|string|max:500`
    - Tương tự cho `experience.*`, `projects.*`, `certifications.*`
    - `skills_text`: `nullable|string|max:1000`
    - `languages`: `nullable|array|max:10`
    - `languages.*.name`: `nullable|string|max:100`
    - `languages.*.level`: `nullable|string|max:50`
    - `photo`: `nullable|image|mimes:jpg,jpeg,png,webp|max:2048`
    - `template`: `required|in:default,modern,minimal`
  - _Requirements: 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

- [ ]* 2.4 Viết unit test cho `UploadCvRequest`
  - Tạo `tests/Unit/UploadCvRequestTest.php`
  - Test file hợp lệ (pdf, doc, docx ≤ 5 MB) → passes (**Property 2**, **Validates: Requirements 1.3**)
  - Test extension không hợp lệ (txt, jpg, exe) → validation error (**Property 2**, **Validates: Requirements 1.3**)
  - Test file > 5 MB → validation error (**Property 3**, **Validates: Requirements 1.4**)
  - _Requirements: 1.3, 1.4_

- [ ]* 2.5 Viết unit test cho `CvFormRequest`
  - Tạo `tests/Unit/CvFormRequestTest.php`
  - Test `full_name` empty → error; > 255 chars → error (**Property 6**, **Validates: Requirements 2.3**)
  - Test `email` malformed → error; null/empty → passes (**Property 7**, **Validates: Requirements 2.4**)
  - Test `phone` ký tự không hợp lệ → error; > 20 chars → error; null → passes (**Property 7**, **Validates: Requirements 2.7**)
  - Test `photo` extension không hợp lệ → error; > 2 MB → error (**Property 7**, **Validates: Requirements 2.5, 2.6**)
  - Test `template` không hợp lệ → error; ∈ {default, modern, minimal} → passes (**Property 7**, **Validates: Requirements 2.8**)
  - Test `education.0.title` > 150 chars → error tại path `education.0.title` (**Property 18**, **Validates: Requirements 2.1**)
  - Test `experience.0.description` > 500 chars → error tại path `experience.0.description` (**Property 18**)
  - _Requirements: 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

---

### 3. Controller Logic

- [ ] 3.1 Thêm method `cv()` vào `UserController`
  - Mở (hoặc tạo) `app/Http/Controllers/UserController.php`
  - Method `cv()`: load `$user = auth()->user()` với eager load `cvData`; trả về `view('user.cv', compact('user'))`
  - **Không** có `abort(403)` — middleware `EnsureEmployee` đã xử lý
  - _Requirements: 1.1, 6.4_

- [ ] 3.2 Thêm method `updateCv(UploadCvRequest $request)` vào `UserController`
  - Kiểm tra quyền ghi thư mục Storage: đảm bảo `storage/app/public/resume/` tồn tại và có quyền ghi trước khi gọi `storeAs` (trên Linux/macOS: `chmod -R 775 storage`; trên Windows/XAMPP thường không cần)
  - Nếu `$user->resume` tồn tại → `Storage::disk('public')->delete($user->resume)`
  - Tạo filename: `Str::uuid() . '_' . $file->getClientOriginalName()`
  - Lưu file: `$file->storeAs('resume', $filename, 'public')`
  - `$user->update(['resume' => $path])`
  - Wrap trong try/catch: log exception, redirect về `GET user/cv` với error flash nếu thất bại
  - Redirect về `GET user/cv` với success flash khi thành công
  - _Requirements: 1.2, 1.7, 9.1, 9.4_

- [ ] 3.3 Thêm method `viewCv()` vào `UserController`
  - Nếu `$user->resume` null → redirect `GET user/cv` với info flash (Req 1.6)
  - Nếu file không tồn tại trong Storage → redirect `GET user/cv` với error flash (Req 1.8)
  - Trả về `Storage::disk('public')->response($user->resume)` inline
  - _Requirements: 1.5, 1.6, 1.8, 8.4_

- [ ] 3.4 Thêm method `createCv()` vào `UserController`
  - `$cvData = auth()->user()->cvData`
  - Trả về `view('user.create-cv', compact('cvData'))` (null-safe, view xử lý pre-populate)
  - _Requirements: 2.1, 2.2_

- [ ] 3.5 Thêm method `saveCv(CvFormRequest $request)` vào `UserController` — xử lý POST
  - Lấy `$existingPhotoPath = auth()->user()->cvData?->photo_path`
  - Kiểm tra quyền ghi thư mục Storage: đảm bảo `storage/app/public/images/cv/` tồn tại và có quyền ghi trước khi gọi `store` (trên Linux/macOS: `chmod -R 775 storage`; trên Windows/XAMPP thường không cần)
  - Xử lý photo:
    - Nếu có file photo mới: lưu trước → `$newPhotoPath = $request->file('photo')->store('images/cv', 'public')`; sau đó xoá photo cũ nếu tồn tại
    - Nếu không có photo mới: `$newPhotoPath = $existingPhotoPath`
  - Upsert trong try/catch:
    - Thành công: `CvData::updateOrCreate(['user_id' => auth()->id()], [...$validated, 'photo_path' => $newPhotoPath])`
    - Thất bại: nếu `$newPhotoPath !== $existingPhotoPath` → xoá photo mới (tránh orphan); log; return `back()->withInput()->with('error', ...)`
  - Redirect `route('cv.preview')` với success flash
  - _Requirements: 3.1, 4.1, 4.2, 4.3, 4.4, 9.2, 9.5, 9.6_

- [ ] 3.6 Thêm method `showPreview()` vào `UserController` — xử lý GET
  - `$cvData = auth()->user()->cvData`
  - Nếu null → redirect `GET user/cv/create` với info flash (Req 3.3)
  - Kiểm tra `View::exists('cv-templates.' . $cvData->template)`:
    - Nếu không tồn tại: `$template = 'default'` + warning flash (Req 3.5, 7.4)
    - Nếu tồn tại: `$template = $cvData->template`
  - Trả về `view('user.cv-preview', compact('cvData', 'template'))`
  - _Requirements: 3.2, 3.3, 3.4, 3.5_

- [ ] 3.7 Thêm method `downloadPdf()` vào `UserController`
  - `$cvData = auth()->user()->cvData` → nếu null, redirect `GET user/cv/create` với info flash (Req 5.2)
  - Xử lý photo: nếu `photo_path` tồn tại và file tồn tại → encode base64 data URI; nếu không → `$photoBase64 = null` (Req 5.3, 5.4)
  - Kiểm tra template, fallback về `default` nếu view không tồn tại
  - `$pdf = Pdf::loadView('cv-templates.' . $template, compact('cvData', 'photoBase64'))->setPaper('A4', 'portrait')`
  - Wrap trong try/catch: log, redirect `route('cv.preview')` với error flash nếu DomPDF thất bại (Req 5.6)
  - Trả về `$pdf->download('cv-' . auth()->id() . '.pdf')` (Req 5.1)
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 9.3_

- [ ] 3.8 Thêm method `deleteOnlineCv()` vào `UserController`
  - `$cvData = auth()->user()->cvData` → nếu null, redirect `GET user/cv/create`
  - Xoá photo file nếu `photo_path` tồn tại: wrap trong try/catch, log lỗi nếu thất bại (không dừng) (Req 6.3)
  - `$cvData->delete()`
  - Redirect `GET user/cv/create` với success flash
  - _Requirements: 6.2, 6.3_

- [ ] 3.9 Checkpoint — Kiểm tra controller
  - Đảm bảo **không có** `abort(403)` trong bất kỳ method nào của `UserController`
  - Đảm bảo tất cả imports đầy đủ: `CvData`, `CvFormRequest`, `UploadCvRequest`, `Storage`, `Str`, `Pdf`, `View`
  - Đảm bảo `saveCv` và `showPreview` là 2 method riêng biệt (không gộp chung)
  - Chạy `php artisan route:list` để kiểm tra routes đã đăng ký đúng
  - Ensure all tests pass, ask the user if questions arise.

---

### 4. Routes & Middleware Registration

- [ ] 4.1 Đăng ký tất cả CV routes trong `routes/web.php`
  - Thêm route group với middleware `['auth', 'verified', 'employee']`:
    ```php
    Route::middleware(['auth', 'verified', 'employee'])->group(function () {
        Route::get('user/cv',              [UserController::class, 'cv']);
        Route::post('user/cv',             [UserController::class, 'updateCv']);
        Route::get('user/cv/view',         [UserController::class, 'viewCv']);
        Route::get('user/cv/create',       [UserController::class, 'createCv']);
        Route::post('user/cv/preview',     [UserController::class, 'saveCv']);
        Route::get('user/cv/preview',      [UserController::class, 'showPreview'])->name('cv.preview');
        Route::get('user/cv/download',     [UserController::class, 'downloadPdf'])->middleware('throttle:10,1');
        Route::delete('user/cv/online',    [UserController::class, 'deleteOnlineCv']);
    });
    ```
  - Lưu ý: `'employee'` alias phải được đăng ký ở task 2.1 trước khi dùng ở đây
  - _Requirements: 5.7, 8.1, 8.2, 8.3_

- [ ]* 4.2 Viết feature test cho middleware stack
  - Tạo `tests/Feature/CvAuthTest.php`
  - Test unauthenticated request tới mỗi route → HTTP 302 redirect tới `/login` (**Property 16**, **Validates: Requirements 8.1**)
  - Test authenticated employer tới mỗi route → HTTP 403 via `EnsureEmployee` middleware (**Property 17**, **Validates: Requirements 8.3**)
  - Test authenticated employee với email chưa verify → redirect tới email verification notice (**Validates: Requirements 8.2**)
  - _Requirements: 8.1, 8.2, 8.3_

---

### 5. Blade Views

- [ ] 5.1 Tạo view `resources/views/user/cv.blade.php`
  - Form upload file: `<input type="file" name="cv_file" accept=".pdf,.doc,.docx">`
  - Hiển thị tên file hiện tại nếu `$user->resume` không null
  - Summary section: link tới `route('cv.preview')` nếu `$user->cvData` tồn tại; link tới `GET user/cv/view` nếu `$user->resume` tồn tại
  - Hiển thị flash messages (success, error, info)
  - _Requirements: 1.1, 6.4_

- [ ] 5.2 Tạo view `resources/views/user/create-cv.blade.php`
  - Form `action="{{ url('user/cv/preview') }}" method="POST"` với `enctype="multipart/form-data"` và `@csrf`
  - Tất cả các trường: `full_name`, `phone`, `email`, `address`, `objective`, `skills_text`, `photo`, `template` (select với 3 options)
  - Các section repeatable (education, experience, projects, certifications, languages): input names dạng `education[0][title]`, `education[0][subtitle]`, v.v.; nút "Thêm" và "Xoá" dùng JavaScript
  - Pre-populate từ `$cvData` nếu không null: `value="{{ old('full_name', $cvData?->full_name) }}"`
  - Hiển thị validation errors: `@error('education.0.title') ... @enderror`
  - _Requirements: 2.1, 2.2, 7.2_

- [ ] 5.3 Tạo view `resources/views/user/cv-preview.blade.php`
  - Nhúng template: `@include('cv-templates.' . $template, ['cvData' => $cvData, 'photoBase64' => null])`
  - Hiển thị label tên template đang dùng
  - Action buttons:
    - "Chỉnh sửa" → `href="{{ url('user/cv/create') }}"`
    - "Tải PDF" → `href="{{ url('user/cv/download') }}"`
    - "Xoá CV" → form với `action="{{ url('user/cv/online') }}"`, `@method('DELETE')`, `@csrf`
  - Hiển thị flash messages (success, error, warning, info)
  - _Requirements: 3.2, 3.4, 6.1_

- [ ] 5.4 Tạo view `resources/views/cv-templates/default.blade.php`
  - Khai báo `<meta charset="UTF-8">` và CSS `body { font-family: 'DejaVu Sans', sans-serif; }`
  - Render tất cả sections: header (tên, phone, email, address, photo), objective, education, experience, projects, certifications, skills_text, languages
  - Xử lý `$photoBase64`: nếu không null → `<img src="{{ $photoBase64 }}">`, nếu null → ẩn ảnh
  - Layout A4 portrait với CSS: `@page { size: A4; margin: 20mm; }`
  - _Requirements: 5.3, 5.4, 5.5, 7.1, 10.2_

- [ ] 5.5 Tạo view `resources/views/cv-templates/modern.blade.php`
  - Thiết kế "hiện đại": layout 2 cột (sidebar trái + nội dung phải), màu accent, typography khác
  - Khai báo `<meta charset="UTF-8">` và `font-family: 'DejaVu Sans', sans-serif`
  - Render đầy đủ tất cả sections từ `$cvData`, xử lý `$photoBase64`
  - _Requirements: 7.1, 10.2_

- [ ] 5.6 Tạo view `resources/views/cv-templates/minimal.blade.php`
  - Thiết kế "tối giản": 1 cột, không màu nền, typography đơn giản, nhiều whitespace
  - Khai báo `<meta charset="UTF-8">` và `font-family: 'DejaVu Sans', sans-serif`
  - Render đầy đủ tất cả sections từ `$cvData`, xử lý `$photoBase64`
  - _Requirements: 7.1, 10.2_

---

### 6. DomPDF Configuration

- [ ] 6.1 Cài đặt package `barryvdh/laravel-dompdf`
  - Chạy `composer require barryvdh/laravel-dompdf`
  - Publish config: `php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"`
  - _Requirements: 10.1_

- [ ] 6.2 Cấu hình `config/dompdf.php` — format v2.x
  - Mở file `config/dompdf.php` (đã publish ở task 6.1)
  - Đặt cấu hình trong mảng `'options'` (**không dùng** key `'defines'` — đó là cú pháp v1.x bị silently ignored):
    ```php
    'options' => [
        'font_subsetting'           => true,
        'default_font'              => 'DejaVu Sans',
        'default_paper_size'        => 'A4',
        'default_paper_orientation' => 'portrait',
        'isHtml5ParserEnabled'      => true,
        'isRemoteEnabled'           => false,
    ],
    ```
  - _Requirements: 10.1, 10.4_

---

### 7. Tests

- [ ] 7.1 Viết feature test cho luồng upload CV file
  - Tạo `tests/Feature/CvUploadTest.php`
  - Test upload file hợp lệ → file được lưu với UUID prefix, `users.resume` cập nhật, redirect success (**Property 1**, **Validates: Requirements 1.2**)
  - Test upload file mới khi đã có file cũ → file cũ bị xoá (**Property 4**, **Validates: Requirements 1.7**)
  - Test extension không hợp lệ → HTTP 422, `users.resume` không thay đổi (**Property 2**, **Validates: Requirements 1.3**)
  - Test file > 5 MB → HTTP 422 (**Property 3**, **Validates: Requirements 1.4**)
  - _Requirements: 1.2, 1.3, 1.4, 1.7, 9.1, 9.4_

- [ ] 7.2 Viết feature test cho luồng tạo CV online
  - Tạo `tests/Feature/CvOnlineCreateTest.php`
  - Test `GET user/cv/create` khi có `cv_data` → form pre-populate đúng (**Property 5**, **Validates: Requirements 2.2**)
  - Test `POST user/cv/preview` hợp lệ → `cv_data` upsert, redirect `cv.preview` với success flash (**Property 8**, **Validates: Requirements 4.1**)
  - Test `POST user/cv/preview` nhiều lần → `cv_data` count = 1 (**Property 10**, **Validates: Requirements 4.5**)
  - Test `POST user/cv/preview` với photo mới → photo cũ bị xoá (**Property 11**, **Validates: Requirements 4.3**)
  - Test `POST user/cv/preview` khi DB upsert thất bại → photo mới bị xoá (orphan cleanup), form trả về với `withInput()`
  - Test `education.0.title` > 150 chars → validation error tại path `education.0.title` (**Property 18**)
  - _Requirements: 2.1, 2.2, 3.1, 4.1, 4.3, 4.5, 9.2, 9.5, 9.6_

- [ ] 7.3 Viết feature test cho preview và download PDF
  - Tạo `tests/Feature/CvPreviewTest.php`
  - Test `GET user/cv/preview` khi có `cv_data` → render đúng template
  - Test `GET user/cv/preview` khi không có `cv_data` → redirect `GET user/cv/create`
  - Test `GET user/cv/preview` khi template không tồn tại → fallback `default` + warning flash (**Validates: Requirements 3.5, 7.4**)
  - Tạo `tests/Feature/CvDownloadTest.php`
  - Test `GET user/cv/download` khi có `cv_data` → response PDF với filename `cv-{user_id}.pdf` (**Property 12**, **Validates: Requirements 5.1**)
  - Test `GET user/cv/download` khi không có `cv_data` → redirect `GET user/cv/create`
  - Test rate limiting: 11 requests trong 1 phút → request thứ 11 nhận HTTP 429 (**Property 14**, **Validates: Requirements 5.7**)
  - _Requirements: 3.2, 3.3, 3.5, 5.1, 5.2, 5.6, 5.7, 7.3, 7.4_

- [ ] 7.4 Viết feature test cho xoá CV online
  - Tạo `tests/Feature/CvDeleteTest.php`
  - Test `DELETE user/cv/online` thành công → `cv_data` bị xoá, redirect success (**Property 15**, **Validates: Requirements 6.2**)
  - Test `DELETE user/cv/online` khi photo file không tồn tại trong Storage → vẫn xoá DB record, không throw exception (**Validates: Requirements 6.3**)
  - Test `DELETE user/cv/online` không có CSRF token → HTTP 419 (đảm bảo form có `@csrf`)
  - _Requirements: 6.2, 6.3_

- [ ] 7.5 Checkpoint cuối — Đảm bảo toàn bộ test suite pass
  - Chạy `php artisan test` để chạy tất cả Feature và Unit tests
  - Đảm bảo không có test nào fail
  - Ensure all tests pass, ask the user if questions arise.

---

## Notes

- Tasks đánh dấu `*` là optional — có thể bỏ qua để triển khai MVP nhanh hơn
- Mỗi task tham chiếu requirements cụ thể để đảm bảo traceability
- **Không** đặt `abort(403)` trong controller — dùng middleware `EnsureEmployee` (task 2.1)
- **Không** dùng key `defines` trong `config/dompdf.php` — dùng `options` (format v2.x)
- `saveCv` và `showPreview` là 2 method riêng biệt, không gộp chung
- DomPDF phải được cài (task 6.1) trước khi implement `downloadPdf()` (task 3.7)
- Middleware `EnsureEmployee` phải được đăng ký (task 2.1) trước khi dùng trong routes (task 4.1)

## Task Dependency Graph

```json
{
  "waves": [
    { "wave": 1, "tasks": ["1.1", "2.1", "2.2", "2.3", "6.1"] },
    { "wave": 2, "tasks": ["1.2", "1.3", "6.2"] },
    { "wave": 3, "tasks": ["3.1", "3.2", "3.3", "3.4", "3.5", "3.6", "3.7", "3.8"] },
    { "wave": 4, "tasks": ["4.1"] },
    { "wave": 5, "tasks": ["5.1", "5.2", "5.3", "5.4", "5.5", "5.6"] },
    { "wave": 6, "tasks": ["3.9", "1.4", "2.4", "2.5", "4.2", "7.1", "7.2", "7.3", "7.4", "7.5"] }
  ]
}
```
