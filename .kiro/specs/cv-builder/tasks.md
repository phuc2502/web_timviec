# Implementation Plan: CV Builder

## Overview

Triển khai module CV Builder trên Laravel 11 + Blade, bao gồm hai luồng chính: upload CV file và tạo CV online với DomPDF. Các task được chia theo nhóm logic, mỗi task xây dựng trên kết quả của task trước, kết thúc bằng việc kết nối toàn bộ hệ thống.

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
  - Tạo `tests/Unit/CvDataModelTest.php`
  - Test JSON casts: lưu array vào JSON column, đọc lại phải trả về array tương đương (**Property 9: JSON columns serialization round-trip**, **Validates: Requirements 4.2**)
  - Test upsert uniqueness: gọi `updateOrCreate` N lần với cùng `user_id`, verify `cv_data` count = 1 (**Property 10: One cv_data record per employee**, **Validates: Requirements 4.5**)
  - _Requirements: 4.2, 4.5_

---

### 2. Form Requests & Validation

- [ ] 2.1 Tạo `UploadCvRequest`
  - Tạo file `app/Http/Requests/UploadCvRequest.php`
  - Khai báo `authorize()` trả về `true`
  - Định nghĩa rules: `cv_file` → `required|file|mimes:pdf,doc,docx|max:5120`
  - _Requirements: 1.3, 1.4_

- [ ] 2.2 Tạo `CvFormRequest`
  - Tạo file `app/Http/Requests/CvFormRequest.php`
  - Khai báo `authorize()` trả về `true`
  - Định nghĩa đầy đủ rules cho tất cả các trường theo design:
    - `full_name`: `required|string|max:255`
    - `phone`: `nullable|regex:/^[\d\s\+\-\(\)]{0,20}$/`
    - `email`: `nullable|email|max:255`
    - `address`: `nullable|string|max:500`
    - `objective`: `nullable|string`
    - `education`, `experience`, `projects`, `certifications`, `languages`: `nullable|array` + `.*` rules
    - `skills_text`: `nullable|string`
    - `photo`: `nullable|image|mimes:jpg,jpeg,png,webp|max:2048`
    - `template`: `required|in:default,modern,minimal`
  - _Requirements: 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

- [ ]* 2.3 Viết unit test cho `UploadCvRequest`
  - Tạo `tests/Unit/UploadCvRequestTest.php`
  - Test với file hợp lệ (pdf, doc, docx ≤ 5 MB) → passes (**Property 2: Invalid file extension rejection**, **Validates: Requirements 1.3**)
  - Test với extension không hợp lệ (txt, jpg, exe) → validation error (**Property 2**, **Validates: Requirements 1.3**)
  - Test với file > 5 MB → validation error (**Property 3: File size limit enforcement**, **Validates: Requirements 1.4**)
  - _Requirements: 1.3, 1.4_

- [ ]* 2.4 Viết unit test cho `CvFormRequest`
  - Tạo `tests/Unit/CvFormRequestTest.php`
  - Test `full_name` empty → error; `full_name` > 255 chars → error; valid → passes (**Property 6**, **Validates: Requirements 2.3**)
  - Test `email` malformed → error; `email` null/empty → passes (**Property 7**, **Validates: Requirements 2.4**)
  - Test `phone` với ký tự không hợp lệ → error; `phone` > 20 chars → error; `phone` null → passes (**Property 7**, **Validates: Requirements 2.7**)
  - Test `photo` extension không hợp lệ → error; `photo` > 2 MB → error (**Property 7**, **Validates: Requirements 2.5, 2.6**)
  - Test `template` không hợp lệ → error; `template` ∈ {default, modern, minimal} → passes (**Property 7**, **Validates: Requirements 2.8**)
  - _Requirements: 2.3, 2.4, 2.5, 2.6, 2.7, 2.8_

---

### 3. Controller Logic

- [ ] 3.1 Thêm method `cv()` vào `UserController`
  - Mở (hoặc tạo) `app/Http/Controllers/UserController.php`
  - Thêm method `cv()`: kiểm tra employer → abort(403); load `$user = auth()->user()`; trả về `view('user.cv', compact('user'))`
  - _Requirements: 1.1, 6.4, 8.3_

- [ ] 3.2 Thêm method `updateCv(UploadCvRequest $request)` vào `UserController`
  - Kiểm tra employer → abort(403)
  - Nếu `$user->resume` tồn tại → `Storage::disk('public')->delete($user->resume)`
  - Tạo filename: `Str::uuid() . '_' . $file->getClientOriginalName()`
  - Lưu file: `$file->storeAs('resume', $filename, 'public')`
  - `$user->update(['resume' => $path])`
  - Wrap trong try/catch: log exception, redirect về `GET user/cv` với error flash nếu thất bại
  - Redirect về `GET user/cv` với success flash khi thành công
  - _Requirements: 1.2, 1.7, 8.3, 9.1, 9.4_

- [ ] 3.3 Thêm method `viewCv()` vào `UserController`
  - Kiểm tra employer → abort(403)
  - Nếu `$user->resume` null → redirect `GET user/cv` với info flash (Req 1.6)
  - Nếu file không tồn tại trong Storage → redirect `GET user/cv` với error flash (Req 1.8)
  - Trả về `Storage::disk('public')->response($user->resume)` inline
  - _Requirements: 1.5, 1.6, 1.8, 8.3, 8.4_

- [ ] 3.4 Thêm method `createCv()` vào `UserController`
  - Kiểm tra employer → abort(403)
  - `$cvData = auth()->user()->cvData`
  - Trả về `view('user.create-cv', compact('cvData'))` (null-safe, view xử lý pre-populate)
  - _Requirements: 2.1, 2.2, 8.3_

- [ ] 3.5 Thêm method `showPreview(Request $request)` vào `UserController` — xử lý cả POST và GET
  - **POST branch:**
    - Kiểm tra employer → abort(403)
    - Validate qua `CvFormRequest` (inject hoặc validate thủ công)
    - Xử lý photo: nếu có file mới → xoá photo cũ (nếu có), lưu photo mới vào `images/cv/`; nếu không → giữ nguyên `photo_path` cũ
    - `CvData::updateOrCreate(['user_id' => auth()->id()], [...$validated, 'photo_path' => $photoPath])`
    - Wrap trong try/catch cho cả photo upload và DB upsert: log, return form với error flash + `withInput()`
    - Redirect `GET user/cv/preview` với success flash
  - **GET branch:**
    - `$cvData = auth()->user()->cvData`
    - Nếu null → redirect `GET user/cv/create` với info flash
    - Kiểm tra `View::exists('cv-templates.' . $cvData->template)` → fallback về `default` + warning flash nếu không tồn tại
    - Trả về `view('user.cv-preview', compact('cvData', 'template'))`
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.6, 8.3, 9.2, 9.5, 9.6_

- [ ] 3.6 Thêm method `downloadPdf()` vào `UserController`
  - Kiểm tra employer → abort(403)
  - `$cvData = auth()->user()->cvData` → nếu null, redirect `GET user/cv/create` với info flash
  - Xử lý photo: nếu `photo_path` tồn tại và file tồn tại → encode base64 data URI; nếu không → `$photoBase64 = null`
  - Kiểm tra template, fallback về `default` nếu view không tồn tại
  - `$pdf = Pdf::loadView('cv-templates.' . $template, compact('cvData', 'photoBase64'))->setPaper('A4', 'portrait')`
  - Wrap trong try/catch: log, redirect `GET user/cv/preview` với error flash nếu DomPDF thất bại
  - Trả về `$pdf->download('cv-' . auth()->id() . '.pdf')`
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 8.3, 9.3_

- [ ] 3.7 Thêm method `deleteOnlineCv()` vào `UserController`
  - Kiểm tra employer → abort(403)
  - `$cvData = auth()->user()->cvData` → nếu null, redirect `GET user/cv/create`
  - Xoá photo file nếu `photo_path` tồn tại: wrap trong try/catch, log lỗi nếu thất bại (không dừng)
  - `$cvData->delete()`
  - Redirect `GET user/cv/create` với success flash
  - _Requirements: 6.2, 6.3, 8.3_

- [ ] 3.8 Checkpoint — Kiểm tra logic controller
  - Đảm bảo tất cả methods đã được thêm vào `UserController`
  - Đảm bảo tất cả imports (use statements) đầy đủ: `CvData`, `CvFormRequest`, `UploadCvRequest`, `Storage`, `Str`, `Pdf`, `View`
  - Chạy `php artisan test --filter UserController` để kiểm tra không có lỗi syntax
  - Ensure all tests pass, ask the user if questions arise.

---

### 4. Routes & Middleware

- [ ] 4.1 Đăng ký tất cả CV routes trong `routes/web.php`
  - Mở `routes/web.php`
  - Thêm route group với middleware `['auth', 'verified']`:
    ```php
    Route::get('user/cv',           [UserController::class, 'cv']);
    Route::post('user/cv',          [UserController::class, 'updateCv']);
    Route::get('user/cv/view',      [UserController::class, 'viewCv']);
    Route::get('user/cv/create',    [UserController::class, 'createCv']);
    Route::post('user/cv/preview',  [UserController::class, 'showPreview']);
    Route::get('user/cv/preview',   [UserController::class, 'showPreview'])->name('cv.preview');
    Route::get('user/cv/download',  [UserController::class, 'downloadPdf'])->middleware('throttle:10,1');
    Route::delete('user/cv/online', [UserController::class, 'deleteOnlineCv']);
    ```
  - _Requirements: 5.7, 8.1, 8.2_

- [ ]* 4.2 Viết feature test cho auth & employer middleware
  - Tạo `tests/Feature/CvAuthTest.php`
  - Test unauthenticated request tới mỗi route → HTTP 302 redirect tới `/login` (**Property 16: Auth middleware protects all CV routes**, **Validates: Requirements 8.1**)
  - Test authenticated user với `user_type = 'employer'` tới mỗi route → HTTP 403 (**Property 17: Employer access returns 403**, **Validates: Requirements 8.3**)
  - _Requirements: 8.1, 8.2, 8.3_

---

### 5. Blade Views

- [ ] 5.1 Tạo view `resources/views/user/cv.blade.php`
  - Form upload file: `<input type="file" name="cv_file" accept=".pdf,.doc,.docx">`
  - Hiển thị tên file hiện tại nếu `$user->resume` không null
  - Summary section: link tới `GET user/cv/preview` nếu `$user->cvData` tồn tại; link tới `GET user/cv/view` nếu `$user->resume` tồn tại
  - Hiển thị flash messages (success, error, info)
  - _Requirements: 1.1, 6.4_

- [ ] 5.2 Tạo view `resources/views/user/create-cv.blade.php`
  - Form `POST user/cv/preview` với `enctype="multipart/form-data"`
  - Tất cả các trường theo Requirement 2.1: `full_name`, `phone`, `email`, `address`, `objective`, `skills_text`, `photo`, `template` (select với 3 options)
  - Các section repeatable (education, experience, projects, certifications, languages): mỗi section có nút "Thêm" và "Xoá" dùng JavaScript để thêm/xoá dòng input
  - Pre-populate tất cả trường từ `$cvData` nếu không null (dùng `old()` với fallback về `$cvData->field`)
  - Hiển thị validation errors
  - _Requirements: 2.1, 2.2, 7.2_

- [ ] 5.3 Tạo view `resources/views/user/cv-preview.blade.php`
  - Nhúng template: `@include('cv-templates.' . $template, ['cvData' => $cvData])`
  - Hiển thị label tên template đang dùng
  - Action buttons: "Chỉnh sửa" → `GET user/cv/create`; "Tải PDF" → `GET user/cv/download`; "Xoá CV" → form `DELETE user/cv/online` với `@method('DELETE')` và `@csrf`
  - Hiển thị flash messages (success, error, warning, info)
  - _Requirements: 3.2, 3.4, 6.1_

- [ ] 5.4 Tạo view `resources/views/cv-templates/default.blade.php`
  - Khai báo `<meta charset="UTF-8">` và CSS `font-family: 'DejaVu Sans', sans-serif`
  - Render tất cả các section CV từ `$cvData`: header (tên, phone, email, address, photo), objective, education, experience, projects, certifications, skills_text, languages
  - Xử lý `$photoBase64`: nếu không null → `<img src="{{ $photoBase64 }}">`, nếu null → ẩn ảnh
  - Layout A4 portrait với CSS phù hợp cho PDF
  - _Requirements: 5.3, 5.4, 5.5, 7.1, 10.2_

- [ ] 5.5 Tạo view `resources/views/cv-templates/modern.blade.php`
  - Tương tự `default.blade.php` nhưng với thiết kế "hiện đại" (sidebar, màu sắc, typography khác)
  - Khai báo `<meta charset="UTF-8">` và `font-family: 'DejaVu Sans', sans-serif`
  - Render đầy đủ tất cả các section CV từ `$cvData`
  - _Requirements: 7.1, 10.2_

- [ ] 5.6 Tạo view `resources/views/cv-templates/minimal.blade.php`
  - Tương tự `default.blade.php` nhưng với thiết kế "tối giản" (ít màu, typography đơn giản)
  - Khai báo `<meta charset="UTF-8">` và `font-family: 'DejaVu Sans', sans-serif`
  - Render đầy đủ tất cả các section CV từ `$cvData`
  - _Requirements: 7.1, 10.2_

---

### 6. DomPDF Configuration

- [ ] 6.1 Cài đặt package `barryvdh/laravel-dompdf`
  - Chạy `composer require barryvdh/laravel-dompdf`
  - Publish config: `php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"`
  - _Requirements: 10.1_

- [ ] 6.2 Cấu hình `config/dompdf.php`
  - Mở file `config/dompdf.php` (đã được publish ở bước 6.1)
  - Đặt `'show_warnings' => false`
  - Trong mảng `'defines'`: set `'DOMPDF_ENABLE_FONT_SUBSETTING' => true`, `'DOMPDF_DEFAULT_FONT' => 'DejaVu Sans'`, `'DOMPDF_DEFAULT_PAPER_SIZE' => 'A4'`, `'DOMPDF_DEFAULT_PAPER_ORIENTATION' => 'portrait'`
  - _Requirements: 10.1, 10.4_

---

### 7. Tests

- [ ] 7.1 Viết feature test cho luồng upload CV file
  - Tạo `tests/Feature/CvUploadTest.php`
  - Test upload file hợp lệ → file được lưu, `users.resume` được cập nhật, redirect với success flash (**Property 1: UUID filename uniqueness**, **Validates: Requirements 1.2**)
  - Test upload file mới khi đã có file cũ → file cũ bị xoá, file mới được lưu (**Property 4: Old file cleanup on re-upload**, **Validates: Requirements 1.7**)
  - Test upload extension không hợp lệ → HTTP 422, `users.resume` không thay đổi (**Property 2**, **Validates: Requirements 1.3**)
  - Test upload file > 5 MB → HTTP 422 (**Property 3**, **Validates: Requirements 1.4**)
  - _Requirements: 1.2, 1.3, 1.4, 1.7, 9.1, 9.4_

- [ ] 7.2 Viết feature test cho luồng tạo CV online (form + save)
  - Tạo `tests/Feature/CvOnlineCreateTest.php`
  - Test `GET user/cv/create` khi có `cv_data` → form được pre-populate đúng (**Property 5: Form pre-population round-trip**, **Validates: Requirements 2.2, 7.2**)
  - Test `POST user/cv/preview` với dữ liệu hợp lệ → `cv_data` được upsert, redirect với success flash (**Property 8: CV data persistence round-trip**, **Validates: Requirements 4.1, 3.2**)
  - Test `POST user/cv/preview` nhiều lần với cùng user → `cv_data` count = 1 (**Property 10**, **Validates: Requirements 4.5**)
  - Test `POST user/cv/preview` với photo mới khi đã có photo cũ → photo cũ bị xoá (**Property 11: Photo cleanup on update**, **Validates: Requirements 4.3**)
  - Test validation errors được trả về đúng field
  - _Requirements: 2.1, 2.2, 3.1, 4.1, 4.3, 4.5, 9.2, 9.5, 9.6_

- [ ] 7.3 Viết feature test cho preview và download PDF
  - Tạo `tests/Feature/CvPreviewTest.php`
  - Test `GET user/cv/preview` khi có `cv_data` → render đúng template
  - Test `GET user/cv/preview` khi không có `cv_data` → redirect `GET user/cv/create`
  - Test `GET user/cv/preview` khi template không tồn tại → fallback về `default` + warning flash (**Validates: Requirements 3.5, 7.4**)
  - Tạo `tests/Feature/CvDownloadTest.php`
  - Test `GET user/cv/download` khi có `cv_data` → response là PDF với filename `cv-{user_id}.pdf` (**Property 12: PDF filename convention**, **Validates: Requirements 5.1**)
  - Test `GET user/cv/download` khi không có `cv_data` → redirect `GET user/cv/create`
  - Test rate limiting: gửi 11 requests trong 1 phút → request thứ 11 nhận HTTP 429 (**Property 14: Rate limiting enforcement**, **Validates: Requirements 5.7**)
  - _Requirements: 3.2, 3.3, 3.5, 5.1, 5.2, 5.6, 5.7, 7.3, 7.4_

- [ ] 7.4 Viết feature test cho xoá CV online
  - Tạo `tests/Feature/CvDeleteTest.php`
  - Test `DELETE user/cv/online` → `cv_data` record bị xoá, redirect với success flash (**Property 15: CV deletion removes record and photo**, **Validates: Requirements 6.2**)
  - Test `DELETE user/cv/online` khi photo file không tồn tại → vẫn xoá DB record, không throw exception (**Validates: Requirements 6.3**)
  - _Requirements: 6.2, 6.3_

- [ ] 7.5 Checkpoint cuối — Đảm bảo toàn bộ test suite pass
  - Chạy `php artisan test` để chạy tất cả Feature và Unit tests
  - Đảm bảo không có test nào fail
  - Ensure all tests pass, ask the user if questions arise.

---

## Notes

- Tasks đánh dấu `*` là optional và có thể bỏ qua để triển khai MVP nhanh hơn
- Mỗi task tham chiếu đến requirements cụ thể để đảm bảo traceability
- Các property test được đặt gần task implementation tương ứng để phát hiện lỗi sớm
- Thứ tự thực hiện: Database → Form Requests → Controller → Routes → Views → DomPDF → Tests
- Tất cả controller methods cần kiểm tra `user_type === 'employer'` → abort(403) ở đầu method
- DomPDF cần được cài đặt (task 6.1) trước khi implement `downloadPdf()` (task 3.6)

## Task Dependency Graph

```json
{
  "waves": [
    { "wave": 1, "tasks": ["1.1", "2.1", "2.2", "6.1"] },
    { "wave": 2, "tasks": ["1.2", "1.3", "6.2"] },
    { "wave": 3, "tasks": ["3.1", "3.2", "3.3", "3.4", "3.5", "3.6", "3.7"] },
    { "wave": 4, "tasks": ["4.1"] },
    { "wave": 5, "tasks": ["5.1", "5.2", "5.3", "5.4", "5.5", "5.6"] },
    { "wave": 6, "tasks": ["3.8", "1.4", "2.3", "2.4", "4.2", "7.1", "7.2", "7.3", "7.4", "7.5"] }
  ]
}
```
