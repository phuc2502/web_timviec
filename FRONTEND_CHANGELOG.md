# Báo cáo Triển khai Frontend - ITWorks (Dự án Tìm Việc)

Tài liệu này tổng hợp toàn bộ các thiết lập, kiến trúc và tiến độ hoàn thiện phần Frontend (Giao diện người dùng) của dự án tính đến thời điểm hiện tại.

## 1. Kiến trúc Mock Data (Chạy không cần Database)
Để tăng tốc quá trình phát triển UI mà không bị phụ thuộc vào tiến độ làm Database, hệ thống đã được thiết lập một cơ chế "Mock Data" hoàn chỉnh:

*   **File `.env`**: Cấu hình `SESSION_DRIVER=file` và `CACHE_STORE=file`.
*   **FakeAuth Middleware**: Middleware `app/Http/Middleware/FakeAuth.php` tự động khởi tạo và inject một tài khoản giả (fake user) vào Auth Guard của Laravel.
    *   Cho phép truy cập các trang yêu cầu đăng nhập (Dashboard, Profile, CV...) mà không cần đăng nhập thật.
    *   **Mẹo Test**: Có thể thay đổi luồng giao diện bằng cách thêm query string vào URL: 
        *   `?type=employee` (Giao diện ứng viên)
        *   `?type=employer` (Giao diện nhà tuyển dụng).
*   **Routes (`routes/web.php`)**: Các dữ liệu hiển thị trên view (Job Listings, User Profile) được giả lập hoàn toàn thông qua các hàm helper như `mockListings()` và `mockUser()`.

## 2. Hệ thống CSS & UI Design
Dự án sử dụng phương pháp **Vanilla CSS** với các biến toàn cục (CSS Variables) được viết thẳng vào `public/css/app.css` để có thể preview ngay lập tức mà không cần chạy trình biên dịch Vite (`npm run dev`).

*   **Bảng màu (Đồng bộ theo THEME.md)**:
    *   Màu chủ đạo: `#00B14F` (TopCV Green).
    *   Hệ thống Neutral Colors: Trắng, Xám (`#F5F5F5`), Border (`#E8E8E8`).
*   **Typography**: Sử dụng font chữ hiện đại `Inter`.
*   **Các UI Components đã thiết kế trong CSS**:
    *   **Buttons** (`.btn`, `.btn-primary`, `.btn-outline`...)
    *   **Badges/Tags** (`.badge`, `.tag-green`, `.tag-gray`...)
    *   **Cards & Layout Utilities** (`.card`, `.grid-3`, `.flex-between`...)
    *   **Form Controls** (`.form-control`, `.search-box`)

## 3. Cấu trúc Blade Layouts
Dự án được chia thành 2 layout chính:

### a) Layout Chính (`layouts/app.blade.php`)
*   Sử dụng cho các trang công khai: Trang chủ, Danh sách việc làm, Chi tiết việc làm.
*   **Thành phần**:
    *   **Navbar**: Thanh điều hướng trên cùng, tự động nhận diện trạng thái `@auth` để thay đổi menu (Đăng nhập/Đăng ký vs Dropdown tài khoản).
    *   **Footer**: Chân trang chuẩn mực với các liên kết bản quyền, mạng xã hội.
    *   **Flash Messages**: Khối hiển thị thông báo góc phải màn hình (`session('success')`, `session('error')`).

### b) Layout Dashboard (`layouts/dashboard.blade.php`)
*   Sử dụng cho các trang quản trị cá nhân sau khi đăng nhập.
*   **Cấu trúc (`.dash-layout`)**: Grid 2 cột (Sidebar 260px và Content).
*   **Sidebar (`.dash-sidebar`)**: Menu điều hướng bên trái, tự động render khác nhau tùy theo `user_type` (Nhà tuyển dụng sẽ thấy Quản lý tin đăng, Ứng viên sẽ thấy Quản lý CV).

## 4. Các module đã ánh xạ (Routing & Navigation)
Tất cả các trang quan trọng hiện tại đã được link thông suốt với nhau:
*   `/` và `/job`: Trang chủ / Tìm kiếm việc làm (Giao diện đã hiển thị đầy đủ danh sách job card).
*   `/job/create`: Đăng tin tuyển dụng mới.
*   `/dashboard`: Bảng điều khiển (Tổng quan).
*   `/user/profile`: Thông tin cá nhân.
*   `/user/cv`: Quản lý CV đã tải lên.
*   `/user/cv/create`: Tạo CV online.
*   `/applicants`: Quản lý danh sách ứng viên đã nộp (hoặc việc đã ứng tuyển).
*   `/subscribe`: Trang nâng cấp gói Premium.
*   `/messages`: Tin nhắn.

## 5. Hướng phát triển tiếp theo (Next Steps)
1.  **Chuyển đổi sang Database thật**: Xóa các hàm `mockListings()` trong `web.php` và thay thế bằng Eloquent Query gọi từ Database (sau khi chạy Migration).
2.  **Cắt HTML/CSS chi tiết cho các trang trong Dashboard**: Hiện tại khung layout đã xong, cần đổ giao diện chi tiết cho từng chức năng như (Form tạo CV, Bảng danh sách ứng viên, Form đăng tin).
3.  **Javascript Validation**: Thêm JS để xử lý validate form ở phía Frontend.
