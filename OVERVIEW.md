# 🏗️ Tổng Quan Dự Án — Website Tìm Việc IT

> **Mô hình:** Laravel MVC thuần  
> **Tham khảo:** Codebase `Tim-test` (tim-vn.tech)  
> **Không dùng AI** — `SuggestController` (OpenAI) bị loại bỏ hoàn toàn

---

## 📋 Mục Lục

| # | Module | Nguồn |
|---|--------|--------|
| 1 | [Auth Module](#1-auth-module) | ✅ Có sẵn |
| 2 | [User Profile](#2-user-profile) | ✅ Có sẵn |
| 3 | [Job Posting](#3-job-posting) | ✅ Có sẵn |
| 4 | [CV Builder](#4-cv-builder) | ✅ Có sẵn |
| 5 | [Search & Filter](#5-search--filter) | ✅ Có sẵn |
| 6 | [Apply & Tracking](#6-apply--tracking) | ✅ Có sẵn |
| 7 | [Notification](#7-notification) | ✅ Có sẵn |
| 8 | [Payment & Subscription](#8-payment--subscription) | ✅ Có sẵn |
| 9 | [Admin Dashboard](#9-admin-dashboard) | 🆕 Xây mới |
| 10 | [Chat & Messaging](#10-chat--messaging) | 🆕 Xây mới |
| — | [Middleware](#-middleware-có-sẵn) | ✅ Có sẵn |
| — | [Database Schema](#-database-schema) | — |
| — | [Tech Stack](#-tech-stack) | — |
| — | [Cấu Trúc Thư Mục](#-cấu-trúc-thư-mục-mvc) | — |

---

## 1. Auth Module

> **File tham khảo:** `app/Http/Controllers/UserController.php`

### Chức năng
- Đăng ký 2 loại tài khoản: **ứng viên** (`employee`) và **nhà tuyển dụng** (`employer`)
- Đăng nhập / Đăng xuất
- Xác minh email (`MustVerifyEmail`)
- Nhà tuyển dụng được tặng **1 tuần dùng thử** (`user_trial = now()->addWeek()`)

### MVC

| Layer | File |
|---|---|
| Model | `app/Models/User.php` |
| Controller | `app/Http/Controllers/UserController.php` |
| Request | `app/Http/Requests/RegistrationFormRequest.php` |
| View | `resources/views/user/{login, register, tim-register, employer-register}.blade.php` |

### Routes

```php
GET  /register            → UserController@register
GET  /register/tim        → UserController@createTim
POST /register/tim        → UserController@storeTim
GET  /register/employer   → UserController@createEmployer
POST /register/employer   → UserController@storeEmployer
GET  /login               → UserController@login
POST /login               → UserController@postLogin
POST /logout              → UserController@logout
GET  /email/verify/{id}/{hash}     → [verify email]   [auth, signed]
GET  /resend/verification/email    → DashboardController@resend
```

### Logic đăng nhập (từ codebase)

```php
// Redirect sau đăng nhập theo user_type
if (user_type == 'employer') → redirect('dashboard')
if (user_type == 'employee') → redirect('/')
```

### Mở rộng thêm
- Đăng nhập Google / GitHub qua **Laravel Socialite**
- Quên mật khẩu dùng built-in Laravel Password Reset

---

## 2. User Profile

> **File tham khảo:** `UserController.php` — `profile()`, `updateProfile()`, `updatePassword()`

### Chức năng
- Cập nhật tên, avatar (`profile_pic`), giới thiệu (`about`)
- Đổi mật khẩu (kiểm tra mật khẩu hiện tại bằng `Hash::check`)
- Upload ảnh lưu vào `storage/public/images`

### Routes

```php
GET  user/profile          → UserController@profile          [auth, verified]
POST user/profile          → UserController@updateProfile    [auth, verified]
POST user/profile/password → UserController@updatePassword   [auth, verified]
```

### Trường `users` liên quan

```
name, about, profile_pic, email, email_verified_at
```

### Mở rộng thêm
- Employer thêm: `company_name`, `company_logo`, `company_website`, `company_size`
- Employee thêm: `skills[]`, `experience_years`, `desired_salary`, `location`

---

## 3. Job Posting

> **File tham khảo:** `PostJobController.php`, `JoblistingController.php`, `app/Post/JobPost.php`

### Chức năng
- CRUD tin tuyển dụng (chỉ employer có gói premium mới được đăng)
- Slug tự động: `Str::slug($title) . '.' . Str::uuid()`
- Ảnh bìa upload lưu `storage/public/images`
- Ngày kết thúc parse từ `m/d/Y` → `Y-m-d` bằng Carbon

### MVC

| Layer | File |
|---|---|
| Model | `app/Models/Listing.php` |
| Service | `app/Post/JobPost.php` (store & updatePost) |
| Controller | `PostJobController.php`, `JoblistingController.php` |
| Request | `JobPostFormRequest.php`, `JobEditFormRequest.php` |
| View | `resources/views/job/{create, edit, index, show}.blade.php` |

### Validation (JobPostFormRequest)

```php
'feature_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
'title'         => 'required',
'description'   => 'required',
'salary'        => 'required',
'address'       => 'required',
'job_type'      => 'required',
'date'          => 'required',
'roles'         => 'required',
```

### Routes

```php
GET  /                        → JoblistingController@index   (trang chủ, paginate 8)
GET  job/show/{listing:slug}  → JoblistingController@show
GET  job/create               → PostJobController@create     [isPremiumUser]
POST job/store                → PostJobController@store      [isPremiumUser]
GET  job/{listing}/edit       → PostJobController@edit
PUT  job/{listing}/update     → PostJobController@update
GET  job/{id}/delete          → PostJobController@destroy
GET  job                      → PostJobController@index      (danh sách job của employer)
```

### Quan hệ Model

```php
// Listing.php
belongsToMany(User::class, 'listing_user')  // ứng viên đã apply
belongsTo(User::class, 'user_id')           // employer đăng bài
```

### Mở rộng thêm
- Thêm trường `status` enum: `open | hidden | closed`
- Auto-close khi hết `application_close_date` bằng Laravel Scheduler

---

## 4. CV Builder

> **File tham khảo:** `UserController.php` — `createCv()`, `previewPDF()`, `updateCv()`, `viewCv()`  
> **Package:** `barryvdh/laravel-dompdf`

### Chức năng
- Upload CV file (`.pdf`, `.doc`, `.docx`) lưu `storage/public/resume`
- Tạo CV online qua form → preview HTML → xuất PDF bằng DomPDF
- Nếu có ảnh đại diện trong form: lưu vào `storage/public/images`

### Luồng hoạt động

```
User điền form (create-cv.blade.php)
    ↓  POST /user/cv/preview
UserController@previewPDF() → lấy $request->all() + xử lý ảnh
    ↓
return view('pdf', compact('data', 'path'))   ← Blade render thành HTML/PDF
```

### Routes

```php
GET  user/cv            → UserController@cv           (upload CV file)
POST user/cv            → UserController@updateCv     (lưu file)
GET  user/cv/view       → UserController@viewCv       (xem file đã upload)
GET  user/cv/create     → UserController@createCv     (form tạo CV online)
GET  user/cv/preview    → UserController@previewPDF   (preview + xuất PDF)
```

### Mở rộng thêm
- Lưu dữ liệu CV vào bảng `cv_data` (JSON hoặc các cột riêng)
- Thêm nhiều template Blade để chọn
- Nút "Tải PDF": `Pdf::loadView('pdf', $data)->download('cv.pdf')`

---

## 5. Search & Filter

> **File tham khảo:** `JoblistingController.php` — `search()`

### Chức năng
- Tìm kiếm theo từ khóa (`title LIKE %keyword%`)
- Lọc: địa điểm, loại hình công việc, mức lương (khoảng)
- Kết quả phân trang 8/trang

### Logic lọc lương (từ codebase)

```php
"Thỏa Thuận"    → salary = 0
"Dưới 5 triệu"  → BETWEEN 1 AND 5,000,000
"5 - 10 triệu"  → BETWEEN 5,000,000 AND 10,000,000
"10 - 15 triệu" → BETWEEN 10,000,000 AND 15,000,000
"Trên 15 triệu" → salary >= 15,000,000
```

### Routes

```php
GET /job/search → JoblistingController@search
    ?search=&address=&job_type=&salary_range=
```

### Mở rộng thêm
- Thêm filter theo `skills` (pivot `listing_skill`)
- Sort: mới nhất / lương cao / deadline gần

---

## 6. Apply & Tracking

> **File tham khảo:** `ApplicantController.php`

### Chức năng
**Ứng viên:**
- Nộp đơn 1-click → `syncWithoutDetaching($listingId)` vào pivot `listing_user`
- Xem danh sách đã ứng tuyển trong Dashboard

**Nhà tuyển dụng:**
- Xem số lượng ứng viên theo từng tin (phân trang 6/trang)
- Shortlist ứng viên: cập nhật `pivot.shortlisted = true`
- Gửi email thông báo cho ứng viên được shortlist (nếu `user.mail = true`)

### Pivot table `listing_user`

```
listing_id (FK) | user_id (FK) | shortlisted (bool) | timestamps
```

### Routes

```php
POST application/{listingId}/submit   → ApplicantController@apply
GET  applicants                       → ApplicantController@index
GET  applicants/{listing:slug}        → ApplicantController@view
POST shortlist/{listingId}/{userId}   → ApplicantController@shortlist
```

### Mở rộng thêm
- Thêm trạng thái chi tiết: `pending → reviewing → interviewed → accepted / rejected`
- Tách ra bảng `applications` để track timeline

---

## 7. Notification

> **File tham khảo:** `app/Mail/ShortlistMail.php`, `PurchaseMail.php`  
> `ApplicantController@shortlist`, `SubscriptionController@paymentSuccess`  
> `DashboardController@mail` (bật/tắt nhận mail)

### Chức năng
- Email khi ứng viên được shortlist → `ShortlistMail` (gửi qua Queue)
- Email xác nhận thanh toán → `PurchaseMail` (gửi qua Queue)
- Ứng viên tự bật/tắt nhận email (`users.mail = true/false`)
- Email xác minh tài khoản khi đăng ký (built-in Laravel)

### Gửi mail

```php
// Queue async, không block request
Mail::to($user->email)->queue(new ShortlistMail(...));
Mail::to(auth()->user())->queue(new PurchaseMail($plan, $billingEnds));
```

### Routes

```php
POST /user/mail → DashboardController@mail  (toggle nhận email)
```

---

## 8. Payment & Subscription

> **File tham khảo:** `SubscriptionController.php`  
> **Tích hợp:** VNPay Sandbox

### Chức năng
- 2 gói premium: **Tháng** (100,000đ) và **Năm** (799,000đ)
- Xây dựng URL thanh toán VNPay → redirect → callback
- Sau thanh toán: cập nhật `status = 'paid'`, `plan`, `billing_ends`, gửi email

### Middleware bảo vệ

```php
// isPremiumUser: kiểm tra còn trong thời hạn dùng thử hoặc gói trả phí
user_trial > today  OR  billing_ends > today  → cho qua
// isEmployer: chỉ employer mới được mua gói
// notAllowPayment: không cho mua nếu đã có gói còn hạn
```

### Trường DB trong `users`

```
user_trial   → date (dùng thử 1 tuần sau đăng ký)
status       → null | 'paid'
plan         → null | 'monthly' | 'yearly'
billing_ends → date
```

### Routes

```php
GET  /subscribe       → SubscriptionController@subscribe
POST pay/monthly      → SubscriptionController@pay
POST pay/yearly       → SubscriptionController@pay
GET  payment/success  → SubscriptionController@paymentSuccess  [signed URL]
```

---

## 9. Admin Dashboard

> **Mới — cần xây dựng**

### Chức năng
- Thống kê: tổng user, tổng tin, tổng đơn ứng tuyển
- Quản lý user: khoá / mở tài khoản
- Duyệt / xoá tin tuyển dụng
- Xem danh sách giao dịch

### MVC cần tạo

```
app/Http/Controllers/Admin/AdminController.php
app/Http/Middleware/IsAdmin.php
resources/views/admin/{index, users, jobs}.blade.php
```

### Trường DB cần thêm vào `users`

```
is_admin → boolean, default false
```

### Routes cần thêm

```php
Route::prefix('admin')->middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/',               [AdminController::class, 'index']);
    Route::get('/users',          [AdminController::class, 'users']);
    Route::post('/users/{id}/ban',[AdminController::class, 'banUser']);
    Route::get('/jobs',           [AdminController::class, 'jobs']);
    Route::delete('/jobs/{id}',   [AdminController::class, 'deleteJob']);
});
```

---

## 10. Chat & Messaging

> **Mới — cần xây dựng**  
> **Realtime:** Pusher hoặc Soketi (self-hosted)

### Chức năng
- Nhà tuyển dụng nhắn tin với ứng viên đã ứng tuyển
- Lưu lịch sử tin nhắn
- Realtime qua Laravel Broadcasting

### MVC cần tạo

```
app/Models/Conversation.php
app/Models/Message.php
app/Http/Controllers/MessageController.php
app/Events/MessageSent.php
resources/views/messages/{index, show}.blade.php
```

### Bảng DB cần tạo

```sql
conversations: id, employer_id, employee_id, listing_id, timestamps
messages: id, conversation_id, sender_id, body, read_at, timestamps
```

### Routes cần thêm

```php
GET  /messages                     → MessageController@index
GET  /messages/{conversation}      → MessageController@show
POST /messages/{conversation}/send → MessageController@send
```

---

## 🔒 Middleware Có Sẵn

| Middleware | File | Logic |
|---|---|---|
| `isEmployer` | `app/Http/Middleware/isEmployer.php` | `user_type === 'employer'` → pass, else abort(401) |
| `isPremiumUser` | `app/Http/Middleware/isPremiumUser.php` | `user_trial > today OR billing_ends > today` → pass, else redirect subscribe |
| `notAllowPayment` | `app/Http/Middleware/notAllowPayment.php` | Không cho mua nếu đã có gói còn hạn |
| `auth` | Built-in Laravel | Yêu cầu đăng nhập |
| `verified` | Built-in Laravel | Yêu cầu xác minh email |

---

## 🗄️ Database Schema

### Bảng hiện có

```
users
  id, name, email, email_verified_at, password
  about, mail(bool), profile_pic, user_type(employee|employer)
  resume, user_trial, billing_ends, status, plan
  remember_token, timestamps

listings
  id, user_id(FK→users)
  title, predes, description, roles
  job_type, address, salary
  application_close_date, feature_image, slug
  timestamps

listing_user (pivot)
  listing_id(FK), user_id(FK), shortlisted(bool), timestamps

personal_access_tokens (Sanctum)
password_reset_tokens
failed_jobs
```

### Bảng cần thêm

```
conversations
  id, employer_id(FK), employee_id(FK), listing_id(FK), timestamps

messages
  id, conversation_id(FK), sender_id(FK), body(text), read_at, timestamps

skills
  id, name, slug, timestamps

listing_skill (pivot)
  listing_id(FK), skill_id(FK)

user_skill (pivot)
  user_id(FK), skill_id(FK)
```

---

## 🛠️ Tech Stack

| Tầng | Công Nghệ | Ghi chú |
|---|---|---|
| Backend | Laravel 11 | MVC framework |
| Auth | Laravel Sanctum + Socialite | Token + OAuth |
| PDF | barryvdh/laravel-dompdf | CV Builder |
| Payment | VNPay SDK | Đã có sandbox |
| Queue | Laravel Queue + Redis | Gửi mail async |
| Realtime | Pusher / Soketi | Chat |
| Search | Laravel Query Builder | LIKE + filter |
| Frontend | Tailwind CSS | MVC thuần |
| DB | MySQL | — |
| Storage | Laravel Storage (public disk) | Local |

---

## 📁 Cấu Trúc Thư Mục MVC

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php           ← Auth + Profile + CV
│   │   ├── JoblistingController.php     ← Trang chủ + Search
│   │   ├── PostJobController.php        ← CRUD tin tuyển dụng
│   │   ├── ApplicantController.php      ← Nộp đơn + Shortlist
│   │   ├── DashboardController.php      ← Dashboard 2 loại user
│   │   ├── SubscriptionController.php   ← VNPay + Gói premium
│   │   ├── MessageController.php        ← [MỚI] Chat
│   │   └── Admin/
│   │       └── AdminController.php      ← [MỚI] Quản trị
│   ├── Middleware/
│   │   ├── isEmployer.php
│   │   ├── isPremiumUser.php
│   │   ├── notAllowPayment.php
│   │   └── IsAdmin.php                  ← [MỚI]
│   └── Requests/
│       ├── RegistrationFormRequest.php
│       ├── JobPostFormRequest.php
│       └── JobEditFormRequest.php
│
├── Models/
│   ├── User.php
│   ├── Listing.php
│   ├── Conversation.php                 ← [MỚI]
│   └── Message.php                      ← [MỚI]
│
├── Mail/
│   ├── ShortlistMail.php
│   └── PurchaseMail.php
│
├── Post/
│   └── JobPost.php                      ← Service class: store + updatePost
│
└── Events/
    └── MessageSent.php                  ← [MỚI]

resources/views/
├── user/          (login, register, profile, cv, create-cv, verify)
├── job/           (show, create, edit, index)
├── applicants/    (index, view)
├── subscription/  (index)
├── dashboard.blade.php
├── pdf.blade.php  (template CV xuất PDF)
├── messages/      ← [MỚI]
└── admin/         ← [MỚI]

database/migrations/
├── create_users_table
├── create_listings_table
├── create_listing_user_table
├── create_skills_table                  ← [MỚI]
├── create_conversations_table           ← [MỚI]
└── create_messages_table                ← [MỚI]

routes/
└── web.php
```

---

## ✅ Tóm Tắt Trạng Thái Module

| Module | Có sẵn | Cần bổ sung | Ưu tiên |
|---|:---:|---|:---:|
| Auth | ✅ | Socialite (Google/GitHub) | 🔴 Cao |
| User Profile | ✅ | Thêm fields employer/employee | 🔴 Cao |
| Job Posting | ✅ | Thêm trường `status` | 🔴 Cao |
| CV Builder | ✅ | Lưu DB, thêm template | 🔴 Cao |
| Search & Filter | ✅ | Thêm filter skill, sort | 🔴 Cao |
| Apply & Tracking | ✅ | Thêm trạng thái chi tiết | 🔴 Cao |
| Notification | ✅ | Thêm job alert email | 🟡 Trung bình |
| Payment (VNPay) | ✅ | Chuyển production keys | 🟡 Trung bình |
| Admin Dashboard | ❌ | Tạo mới hoàn toàn | 🟡 Trung bình |
| Chat & Messaging | ❌ | Tạo mới hoàn toàn | 🟢 Thấp |

