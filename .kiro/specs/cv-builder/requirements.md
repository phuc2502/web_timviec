# Requirements Document

## Introduction

Chức năng CV Builder cho phép ứng viên (employee) trên website tìm việc IT thực hiện hai luồng chính:
1. **Upload CV file** — tải lên file `.pdf`, `.doc`, `.docx` để lưu trữ và chia sẻ với nhà tuyển dụng.
2. **Tạo CV online** — điền form thông tin cá nhân, kinh nghiệm, học vấn, kỹ năng; chọn template Blade; xem preview HTML; xuất PDF bằng `barryvdh/laravel-dompdf`; lưu dữ liệu vào bảng `cv_data`.

Hệ thống được xây dựng trên Laravel 11 + Blade (PHP MVC thuần), không dùng Inertia/React.

**Luồng dữ liệu chính:**
```
POST /user/cv/preview  →  lưu cv_data + photo  →  render preview HTML
GET  /user/cv/preview  →  đọc cv_data từ DB    →  render preview HTML (không cần submit lại form)
GET  /user/cv/download →  đọc cv_data từ DB    →  xuất PDF
```

---

## Glossary

- **CV_Builder**: Hệ thống con xử lý toàn bộ luồng tạo, lưu trữ và xuất CV.
- **UserController**: Controller Laravel xử lý các action liên quan đến CV của user.
- **CvData**: Model Eloquent ánh xạ bảng `cv_data`, lưu nội dung CV online của ứng viên.
- **CvFormRequest**: Form Request Laravel validate dữ liệu form tạo CV online.
- **UploadCvRequest**: Form Request Laravel validate file CV upload.
- **DomPDF**: Package `barryvdh/laravel-dompdf` dùng để render Blade view thành file PDF.
- **Template**: File Blade dùng để render giao diện CV (`default`, `modern`, `minimal`).
- **Employee**: Người dùng có `user_type = 'employee'`, là ứng viên tìm việc.
- **Storage_Public**: Disk `public` của Laravel Storage, ánh xạ tới `storage/app/public/`.
- **Resume_Path**: Đường dẫn tương đối trong Storage_Public, dạng `resume/{filename}`.
- **Photo_Path**: Đường dẫn tương đối trong Storage_Public, dạng `images/cv/{filename}`.
- **Orphan_Photo**: File ảnh đã lưu vào Storage_Public nhưng chưa được ghi vào `cv_data.photo_path` (do user thoát trước khi lưu).

---

## Requirements

### Requirement 1: Upload CV File

**User Story:** As an Employee, I want to upload my CV file, so that employers can download and review my resume directly.

#### Acceptance Criteria

1. WHEN an Employee visits `GET user/cv`, THE CV_Builder SHALL display a form allowing upload of a single file with extensions `.pdf`, `.doc`, or `.docx`, and SHALL show the current uploaded file name if `users.resume` is not null.
2. WHEN an Employee submits a valid CV file via `POST user/cv`, THE UserController SHALL generate a unique filename (using UUID prefix) to prevent collisions, store the file in `storage/app/public/resume/`, and update the `users.resume` column with the Resume_Path.
3. IF the uploaded file has an extension other than `.pdf`, `.doc`, or `.docx`, THEN THE UploadCvRequest SHALL reject the request and return a validation error message specifying the allowed file types.
4. IF the uploaded file size exceeds 5 MB, THEN THE UploadCvRequest SHALL reject the request and return a validation error message specifying the maximum allowed size.
5. WHEN an Employee visits `GET user/cv/view` and `users.resume` is not null and the physical file exists in Storage_Public, THE UserController SHALL serve the file inline so the browser can display or download it.
6. IF an Employee visits `GET user/cv/view` and `users.resume` is null, THEN THE UserController SHALL redirect the Employee to `GET user/cv` with an informational flash message.
7. WHEN a new CV file is uploaded successfully and a previous Resume_Path exists in `users.resume`, THE UserController SHALL delete the previously stored file from Storage_Public before saving the new path. IF no previous file existed, THE UserController SHALL skip the deletion step.
8. IF an Employee visits `GET user/cv/view` and `users.resume` is not null but the physical file does not exist in Storage_Public, THEN THE UserController SHALL redirect the Employee to `GET user/cv` with an error flash message indicating the file is missing.

---

### Requirement 2: Tạo CV Online — Form Nhập Liệu

**User Story:** As an Employee, I want to fill in an online CV form, so that I can create a professional CV without needing a word processor.

#### Acceptance Criteria

1. WHEN an Employee visits `GET user/cv/create`, THE CV_Builder SHALL display a form (`create-cv.blade.php`) containing fields for: `full_name` (required), `phone` (optional), `email` (optional), `address` (optional), `objective` (optional), `education` (repeatable, optional), `experience` (repeatable, optional), `projects` (repeatable, optional), `certifications` (repeatable, optional), `skills_text` (optional), `languages` (repeatable, optional), `photo` (optional image upload), and `template` (required selection).
2. WHEN an Employee visits `GET user/cv/create` and a `cv_data` record exists for the authenticated Employee, THE CV_Builder SHALL pre-populate all form fields with the stored values from that record.
3. IF an Employee submits the form with `full_name` empty or exceeding 255 characters, THEN THE CvFormRequest SHALL reject the request and return a validation error for the `full_name` field.
4. IF an Employee submits the form with `email` present but not matching the format `local-part@domain` (where domain contains at least one dot and the total length does not exceed 255 characters), THEN THE CvFormRequest SHALL reject the request and return a validation error for the `email` field. IF `email` is absent or empty, THE CvFormRequest SHALL accept the submission without error on this field.
5. IF an Employee uploads a `photo` file with an extension other than `.jpg`, `.jpeg`, `.png`, or `.webp`, THEN THE CvFormRequest SHALL reject the request and return a validation error for the `photo` field.
6. IF an Employee uploads a `photo` file exceeding 2 MB, THEN THE CvFormRequest SHALL reject the request and return a validation error for the `photo` field.
7. IF an Employee submits the form with `phone` present and non-empty but containing characters other than digits, spaces, `+`, `-`, `(`, or `)`, or exceeding 20 characters, THEN THE CvFormRequest SHALL reject the request and return a validation error for the `phone` field. IF `phone` is absent or empty, THE CvFormRequest SHALL accept the submission without error on this field.
8. IF an Employee submits the form with a `template` value that is not one of `default`, `modern`, or `minimal`, THEN THE CvFormRequest SHALL reject the request and return a validation error for the `template` field.

---

### Requirement 3: Lưu CV và Preview HTML

**User Story:** As an Employee, I want my CV data saved when I submit the form, and I want to be able to view the preview again later without re-submitting the form.

#### Acceptance Criteria

1. WHEN an Employee submits the CV form via `POST user/cv/preview`, THE UserController SHALL first upsert the `cv_data` record (Requirement 4), then redirect to `GET user/cv/preview` with a success flash message.
2. WHEN an Employee visits `GET user/cv/preview` and a `cv_data` record exists for the authenticated Employee, THE CV_Builder SHALL render the preview Blade view using the template stored in `cv_data.template`, displaying all saved CV fields.
3. IF an Employee visits `GET user/cv/preview` and no `cv_data` record exists, THEN THE UserController SHALL redirect the Employee to `GET user/cv/create` with an informational flash message prompting them to fill in the form first.
4. WHEN the preview page is displayed, THE CV_Builder SHALL show the name of the template currently in use as a visible label so the Employee can identify which template is active.
5. IF the template name stored in `cv_data.template` does not correspond to an existing Blade view file at `resources/views/cv-templates/{template}.blade.php`, THEN THE UserController SHALL render the `default` template AND display a warning flash message informing the Employee that the previously selected template is unavailable and the default has been used instead.

---

### Requirement 4: Lưu Dữ Liệu CV vào Database

**User Story:** As an Employee, I want my CV data to be saved automatically, so that I can return later and continue editing without re-entering information.

#### Acceptance Criteria

1. WHEN an Employee submits the CV form via `POST user/cv/preview`, THE CvData SHALL upsert (create or update) a record in the `cv_data` table keyed on `user_id`, persisting all submitted fields before any redirect or render occurs.
2. WHEN saving to `cv_data`, THE CvData SHALL store `education`, `experience`, `projects`, `certifications`, and `languages` as JSON arrays in their respective JSON columns.
3. WHEN saving to `cv_data` and a new `photo` file is submitted, THE UserController SHALL store the new photo to a new Photo_Path, update `cv_data.photo_path` with the new path, and delete the previously stored photo file from Storage_Public if one existed. IF no new photo is submitted, THE CvData SHALL retain the existing `cv_data.photo_path` value unchanged.
4. WHEN saving to `cv_data`, THE CvData SHALL store the selected `template` value (maximum 50 characters) in the `template` column.
5. THE CvData SHALL enforce a unique constraint on `user_id` so that each Employee has at most one `cv_data` record.
6. IF the database upsert operation fails, THEN THE UserController SHALL catch the exception, log the error, and return the form view with an error flash message and all submitted field values preserved via `withInput()`.

---

### Requirement 5: Xuất PDF

**User Story:** As an Employee, I want to download my saved CV as a PDF, so that I can share it with employers.

#### Acceptance Criteria

1. WHEN an Employee visits `GET user/cv/download` and a `cv_data` record exists, THE UserController SHALL use DomPDF to render the template stored in `cv_data.template` with the saved CV data and return a downloadable PDF file named `cv-{user_id}.pdf`.
2. IF no `cv_data` record exists when an Employee visits `GET user/cv/download`, THEN THE UserController SHALL redirect the Employee to `GET user/cv/create` with an informational flash message.
3. WHEN DomPDF renders the PDF and `cv_data.photo_path` is set and the physical file exists in Storage_Public, THE CV_Builder SHALL read the file and encode it as a base64 data URI before passing it to the template so the image is embedded in the PDF output.
4. IF the photo file referenced by `cv_data.photo_path` does not exist in Storage_Public at PDF render time, THEN THE CV_Builder SHALL pass a null photo value to the template and omit the image from the PDF without returning an error to the Employee.
5. WHEN DomPDF renders the PDF, THE CV_Builder SHALL configure DomPDF to use a Unicode-compatible font (e.g., DejaVu or a bundled Vietnamese-supporting font) so that Vietnamese characters render correctly in the output file.
6. IF DomPDF fails to generate the PDF, THEN THE UserController SHALL catch the exception, log the error, and redirect the Employee to `GET user/cv/preview` with an error flash message — no partial file shall be sent to the browser.
7. THE CV_Builder SHALL apply Laravel's built-in rate limiting (`throttle` middleware) to `GET user/cv/download`, allowing a maximum of 10 PDF generation requests per Employee per minute. IF the limit is exceeded, THE system SHALL return an HTTP 429 response with a message indicating when the Employee may retry.

---

### Requirement 6: Quản Lý CV Đã Tạo

**User Story:** As an Employee, I want a dedicated page to manage my online CV, so that I can view, re-download, or delete it without re-submitting the entire form.

#### Acceptance Criteria

1. WHEN an Employee visits `GET user/cv/preview`, THE CV_Builder SHALL display the rendered CV preview alongside action buttons: "Chỉnh sửa" (links to `GET user/cv/create`), "Tải PDF" (links to `GET user/cv/download`), and "Xoá CV" (triggers `DELETE user/cv/online`).
2. WHEN an Employee submits `DELETE user/cv/online`, THE UserController SHALL delete the `cv_data` record for the authenticated Employee, delete the associated photo file from Storage_Public if `cv_data.photo_path` is set, and redirect to `GET user/cv/create` with a success flash message.
3. IF the photo file deletion fails during `DELETE user/cv/online`, THE UserController SHALL still delete the `cv_data` record, log the file deletion error, and proceed with the redirect — the database record SHALL NOT be retained due to a file cleanup failure.
4. WHEN an Employee visits `GET user/cv` (the upload page), THE CV_Builder SHALL display a summary section showing whether an online CV exists (with a link to `GET user/cv/preview`) and whether an uploaded CV file exists (with a link to `GET user/cv/view`), giving the Employee a single entry point to manage both CV types.

---

### Requirement 7: Chọn Template CV

**User Story:** As an Employee, I want to choose from multiple CV templates, so that I can present my information in a style that suits my profession.

#### Acceptance Criteria

1. THE CV_Builder SHALL provide exactly three selectable templates: `default`, `modern`, and `minimal`, each with a corresponding Blade view at `resources/views/cv-templates/{template}.blade.php`.
2. WHEN an Employee selects a template on the form and submits, THE CvData SHALL persist the selected template name so that the `template` form field is pre-selected with that value on subsequent visits to `GET user/cv/create`.
3. WHEN an Employee visits `GET user/cv/preview`, THE CV_Builder SHALL render the preview using the template name stored in `cv_data.template` — it SHALL NOT re-read the template from request input on this GET route.
4. IF the template stored in `cv_data.template` does not correspond to an existing Blade view file, THEN THE UserController SHALL fall back to `default` AND display a warning flash message (as specified in Requirement 3.5) — silent fallback without notification is not acceptable.

---

### Requirement 8: Bảo Mật và Phân Quyền

**User Story:** As a system administrator, I want CV routes to be protected, so that only authenticated and verified employees can access CV Builder features.

#### Acceptance Criteria

1. THE CV_Builder SHALL apply the `auth` middleware to all routes under `user/cv/*` so that unauthenticated users are redirected to the login page.
2. THE CV_Builder SHALL apply the `verified` middleware to all routes under `user/cv/*` so that users who have not verified their email are redirected to the email verification notice.
3. WHEN an authenticated user with `user_type = 'employer'` attempts to access any `user/cv/*` route, THE UserController SHALL return an HTTP 403 response.
4. WHEN an Employee accesses `GET user/cv/view`, THE UserController SHALL serve only the CV file whose Resume_Path is stored in `users.resume` for the authenticated Employee — it SHALL NOT accept a file path from query parameters or any other request input.
5. WHEN an Employee accesses `GET user/cv/download` or `DELETE user/cv/online`, THE UserController SHALL operate only on the `cv_data` record belonging to the authenticated Employee — it SHALL NOT accept a `user_id` or record identifier from request input.

---

### Requirement 9: Xử Lý Lỗi và Trạng Thái Hệ Thống

**User Story:** As an Employee, I want clear feedback when something goes wrong, so that I understand what action to take next.

#### Acceptance Criteria

1. IF a file storage operation fails during CV file upload (`POST user/cv`), THEN THE UserController SHALL catch the exception, log the error, and return the upload form with an error flash message indicating that the file could not be saved.
2. IF a file storage operation fails during photo upload (`POST user/cv/preview`), THEN THE UserController SHALL catch the exception, log the error, and return the form view with an error flash message while preserving all other submitted field values via `withInput()`.
3. IF DomPDF fails to generate the PDF (`GET user/cv/download`), THEN THE UserController SHALL catch the exception, log the error, and redirect the Employee to `GET user/cv/preview` with an error flash message — no partial file shall be sent to the browser.
4. WHEN a CV file upload (`POST user/cv`) completes successfully, THE CV_Builder SHALL redirect the Employee to `GET user/cv` with a success flash message.
5. WHEN a CV form submission (`POST user/cv/preview`) completes successfully, THE CV_Builder SHALL redirect the Employee to `GET user/cv/preview` with a success flash message indicating the CV data was saved.
6. IF the database upsert fails during `POST user/cv/preview`, THEN THE UserController SHALL catch the exception, log the error, and return the form view with an error flash message and all submitted field values preserved via `withInput()`.

---

### Requirement 10: Cấu Hình DomPDF và Font Tiếng Việt

**User Story:** As an Employee, I want my PDF CV to display Vietnamese text correctly, so that my name, address, and descriptions are readable by employers.

#### Acceptance Criteria

1. WHEN DomPDF is configured in the Laravel application, THE CV_Builder SHALL set `'enable_font_subsetting' => true` and reference a Unicode font that supports the Vietnamese character set (e.g., DejaVu Sans or a custom bundled font) in the DomPDF configuration file (`config/dompdf.php`).
2. WHEN a CV template Blade view is rendered for PDF output, THE CV_Builder SHALL declare `<meta charset="UTF-8">` and apply a CSS `font-family` that references the configured Unicode font so that all Vietnamese characters in the output are rendered correctly.
3. IF the configured Unicode font file is missing from the expected path at application boot, THEN THE CV_Builder SHALL fall back to DomPDF's built-in DejaVu font and log a warning — PDF generation SHALL NOT be blocked by a missing custom font.
4. WHEN DomPDF renders a CV template, THE CV_Builder SHALL set the DomPDF paper size to `A4` and orientation to `portrait` so the output matches standard CV dimensions.
