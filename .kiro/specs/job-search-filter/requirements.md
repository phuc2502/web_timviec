# Requirements Document

## Introduction

Module **Tìm kiếm và Lọc tin tuyển dụng (Search & Filter)** cho phép ứng viên (employee) tìm kiếm tin tuyển dụng IT theo nhiều tiêu chí: từ khóa, địa điểm, loại công việc, mức lương, kỹ năng yêu cầu. Kết quả được sắp xếp linh hoạt và phân trang. Module hoạt động trên nền tảng Laravel 11 + MySQL, tận dụng FULLTEXT INDEX trên bảng `listings` và quan hệ nhiều-nhiều với bảng `skills` qua bảng pivot `listing_skill`.

---

## Glossary

- **Search_Engine**: Thành phần xử lý logic tìm kiếm và lọc tin tuyển dụng phía backend (Laravel Controller + Query Builder).
- **Listing**: Một tin tuyển dụng trong bảng `listings`, có trạng thái `open`, `hidden`, hoặc `closed`.
- **Ứng_viên**: Người dùng có `user_type = 'employee'` đang tìm việc.
- **Từ_khóa**: Chuỗi văn bản nhập vào ô tìm kiếm, dùng để khớp với `title` và `predes` của Listing.
- **Bộ_lọc**: Tập hợp các tham số lọc gồm địa điểm, loại công việc, khoảng lương, và danh sách kỹ năng.
- **Kết_quả_tìm_kiếm**: Danh sách các Listing thỏa mãn Từ_khóa và Bộ_lọc, đã được sắp xếp và phân trang.
- **Trang_kết_quả**: Trang hiển thị Kết_quả_tìm_kiếm, truy cập qua URL `/jobs` hoặc `/jobs/search`.
- **Skill**: Một kỹ năng trong bảng `skills`, liên kết với Listing qua bảng pivot `listing_skill`.
- **Salary**: Mức lương lưu trong cột `salary` (kiểu `BIGINT UNSIGNED`), đơn vị VNĐ; giá trị `0` nghĩa là "Thỏa thuận".
- **Job_Type**: Loại hình công việc lưu trong cột `job_type`, ví dụ: `Full-time`, `Part-time`, `Remote`, `Freelance`.
- **Paginator**: Thành phần phân trang của Laravel, trả về danh sách Listing theo từng trang với metadata tổng số kết quả.

---

## Requirements

### Requirement 1: Tìm kiếm theo từ khóa

**User Story:** Là một Ứng_viên, tôi muốn nhập từ khóa để tìm tin tuyển dụng phù hợp, để tôi có thể nhanh chóng tìm được công việc liên quan đến kỹ năng hoặc vị trí mong muốn.

#### Tiêu chí chấp nhận

1. WHEN Ứng_viên gửi yêu cầu tìm kiếm với tham số `keyword` không rỗng, THE Search_Engine SHALL thực hiện truy vấn FULLTEXT SEARCH trên các cột `title` và `predes` của bảng `listings` với từ khóa đó.
2. WHEN Ứng_viên gửi yêu cầu tìm kiếm với tham số `keyword` rỗng hoặc không có, THE Search_Engine SHALL bỏ qua điều kiện tìm kiếm từ khóa và trả về tất cả Listing thỏa mãn các Bộ_lọc còn lại.
3. WHEN Ứng_viên nhập từ khóa chứa ký tự đặc biệt (ví dụ: `<`, `>`, `"`, `'`, `;`), THE Search_Engine SHALL làm sạch (sanitize) đầu vào trước khi thực hiện truy vấn để ngăn chặn SQL injection.
4. THE Search_Engine SHALL chỉ trả về các Listing có `status = 'open'` trong mọi kết quả tìm kiếm.
5. THE Search_Engine SHALL chỉ trả về các Listing có `application_close_date >= ngày hiện tại` trong mọi kết quả tìm kiếm.

---

### Requirement 2: Lọc theo địa điểm

**User Story:** Là một Ứng_viên, tôi muốn lọc tin tuyển dụng theo địa điểm làm việc, để tôi chỉ xem các công việc phù hợp với nơi tôi có thể đến làm.

#### Tiêu chí chấp nhận

1. WHEN Ứng_viên gửi yêu cầu với tham số `address` không rỗng, THE Search_Engine SHALL lọc các Listing có cột `address` chứa chuỗi địa điểm đó (so khớp không phân biệt hoa thường, dạng `LIKE '%value%'`).
2. WHEN Ứng_viên gửi yêu cầu với tham số `address` rỗng hoặc không có, THE Search_Engine SHALL bỏ qua điều kiện lọc địa điểm.
3. THE Search_Engine SHALL hỗ trợ lọc đồng thời địa điểm cùng với các Bộ_lọc khác (từ khóa, loại công việc, lương, kỹ năng).

---

### Requirement 3: Lọc theo loại công việc

**User Story:** Là một Ứng_viên, tôi muốn lọc tin tuyển dụng theo loại hình công việc (Full-time, Part-time, Remote, Freelance...), để tôi tìm được công việc phù hợp với lịch trình và hình thức làm việc mong muốn.

#### Tiêu chí chấp nhận

1. WHEN Ứng_viên gửi yêu cầu với tham số `job_type` hợp lệ (một trong các giá trị tồn tại trong cột `job_type` của bảng `listings`), THE Search_Engine SHALL lọc các Listing có `job_type` khớp chính xác với giá trị đó.
2. WHEN Ứng_viên gửi yêu cầu với tham số `job_type` rỗng hoặc không có, THE Search_Engine SHALL bỏ qua điều kiện lọc loại công việc.
3. IF Ứng_viên gửi tham số `job_type` với giá trị không tồn tại trong hệ thống, THEN THE Search_Engine SHALL bỏ qua tham số đó và trả về kết quả không áp dụng bộ lọc loại công việc.
4. THE Search_Engine SHALL cung cấp danh sách các giá trị `job_type` hiện có trong bảng `listings` để hiển thị trên giao diện bộ lọc.

---

### Requirement 4: Lọc theo khoảng mức lương

**User Story:** Là một Ứng_viên, tôi muốn lọc tin tuyển dụng theo khoảng mức lương mong muốn, để tôi chỉ xem các công việc có mức lương phù hợp với kỳ vọng của mình.

#### Tiêu chí chấp nhận

1. WHEN Ứng_viên gửi yêu cầu với tham số `salary_min` là số nguyên không âm, THE Search_Engine SHALL lọc các Listing có `salary >= salary_min`, ngoại trừ các Listing có `salary = 0` (Thỏa thuận).
2. WHEN Ứng_viên gửi yêu cầu với tham số `salary_max` là số nguyên dương, THE Search_Engine SHALL lọc các Listing có `salary <= salary_max`, ngoại trừ các Listing có `salary = 0` (Thỏa thuận).
3. WHEN Ứng_viên gửi yêu cầu với cả hai tham số `salary_min` và `salary_max`, THE Search_Engine SHALL lọc các Listing có `salary_min <= salary <= salary_max`, ngoại trừ các Listing có `salary = 0` (Thỏa thuận).
4. WHEN Ứng_viên gửi yêu cầu với tham số `include_negotiable = true`, THE Search_Engine SHALL bao gồm các Listing có `salary = 0` (Thỏa thuận) trong kết quả lọc lương.
5. WHEN Ứng_viên không cung cấp tham số `salary_min` và `salary_max`, THE Search_Engine SHALL bỏ qua điều kiện lọc lương.
6. IF Ứng_viên gửi tham số `salary_min` lớn hơn `salary_max`, THEN THE Search_Engine SHALL trả về lỗi validation với thông báo mô tả rõ ràng rằng `salary_min` không được lớn hơn `salary_max`.

---

### Requirement 5: Lọc theo kỹ năng yêu cầu

**User Story:** Là một Ứng_viên, tôi muốn lọc tin tuyển dụng theo kỹ năng cụ thể (ví dụ: PHP, Laravel, React...), để tôi tìm được công việc phù hợp với bộ kỹ năng của mình.

#### Tiêu chí chấp nhận

1. WHEN Ứng_viên gửi yêu cầu với tham số `skills` là mảng một hoặc nhiều `skill_id` hợp lệ, THE Search_Engine SHALL lọc các Listing có liên kết với TẤT CẢ các Skill trong danh sách đó qua bảng pivot `listing_skill`.
2. WHEN Ứng_viên gửi yêu cầu với tham số `skills` là mảng rỗng hoặc không có, THE Search_Engine SHALL bỏ qua điều kiện lọc kỹ năng.
3. IF Ứng_viên gửi tham số `skills` chứa `skill_id` không tồn tại trong bảng `skills`, THEN THE Search_Engine SHALL bỏ qua các `skill_id` không hợp lệ đó và chỉ áp dụng lọc với các `skill_id` hợp lệ còn lại.
4. THE Search_Engine SHALL cung cấp danh sách tất cả Skill hiện có trong bảng `skills` để hiển thị trên giao diện bộ lọc.

---

### Requirement 6: Sắp xếp kết quả

**User Story:** Là một Ứng_viên, tôi muốn sắp xếp danh sách tin tuyển dụng theo các tiêu chí khác nhau, để tôi có thể ưu tiên xem các tin phù hợp nhất trước.

#### Tiêu chí chấp nhận

1. WHEN Ứng_viên gửi yêu cầu với tham số `sort = 'latest'` hoặc không có tham số `sort`, THE Search_Engine SHALL sắp xếp Kết_quả_tìm_kiếm theo `created_at` giảm dần (mới nhất trước).
2. WHEN Ứng_viên gửi yêu cầu với tham số `sort = 'salary_desc'`, THE Search_Engine SHALL sắp xếp Kết_quả_tìm_kiếm theo `salary` giảm dần, đặt các Listing có `salary = 0` (Thỏa thuận) xuống cuối danh sách.
3. WHEN Ứng_viên gửi yêu cầu với tham số `sort = 'salary_asc'`, THE Search_Engine SHALL sắp xếp Kết_quả_tìm_kiếm theo `salary` tăng dần, đặt các Listing có `salary = 0` (Thỏa thuận) xuống cuối danh sách.
4. WHEN Ứng_viên gửi yêu cầu với tham số `sort = 'closing_soon'`, THE Search_Engine SHALL sắp xếp Kết_quả_tìm_kiếm theo `application_close_date` tăng dần (sắp hết hạn trước).
5. IF Ứng_viên gửi tham số `sort` với giá trị không được hỗ trợ, THEN THE Search_Engine SHALL áp dụng sắp xếp mặc định theo `created_at` giảm dần.

---

### Requirement 7: Phân trang kết quả

**User Story:** Là một Ứng_viên, tôi muốn kết quả tìm kiếm được phân trang, để tôi có thể duyệt qua nhiều tin tuyển dụng mà không bị quá tải thông tin trên một trang.

#### Tiêu chí chấp nhận

1. THE Search_Engine SHALL phân trang Kết_quả_tìm_kiếm với mặc định 15 Listing mỗi trang.
2. WHEN Ứng_viên gửi yêu cầu với tham số `per_page` là số nguyên trong khoảng từ 5 đến 50, THE Search_Engine SHALL phân trang theo số lượng Listing mỗi trang tương ứng.
3. IF Ứng_viên gửi tham số `per_page` ngoài khoảng từ 5 đến 50, THEN THE Search_Engine SHALL sử dụng giá trị mặc định là 15 Listing mỗi trang.
4. THE Paginator SHALL trả về metadata bao gồm: tổng số kết quả (`total`), số trang hiện tại (`current_page`), tổng số trang (`last_page`), và số Listing mỗi trang (`per_page`).
5. THE Search_Engine SHALL giữ nguyên tất cả tham số Bộ_lọc và sắp xếp hiện tại trong URL phân trang để Ứng_viên có thể điều hướng giữa các trang mà không mất bộ lọc.

---

### Requirement 8: Hiển thị thông tin Listing trong kết quả

**User Story:** Là một Ứng_viên, tôi muốn xem đủ thông tin cơ bản của mỗi tin tuyển dụng trong danh sách kết quả, để tôi có thể đánh giá nhanh mà không cần vào từng trang chi tiết.

#### Tiêu chí chấp nhận

1. THE Search_Engine SHALL trả về cho mỗi Listing trong Kết_quả_tìm_kiếm các trường: `id`, `title`, `slug`, `predes`, `job_type`, `address`, `salary`, `application_close_date`, `created_at`, và thông tin nhà tuyển dụng (`company_name`, `company_logo` từ bảng `users`).
2. THE Search_Engine SHALL trả về danh sách Skill liên kết với mỗi Listing (eager loading qua quan hệ `listing_skill`).
3. THE Search_Engine SHALL tải thông tin nhà tuyển dụng bằng eager loading để tránh vấn đề N+1 query.

---

### Requirement 9: Kết hợp nhiều bộ lọc đồng thời

**User Story:** Là một Ứng_viên, tôi muốn áp dụng nhiều tiêu chí lọc cùng lúc, để tôi có thể thu hẹp kết quả tìm kiếm một cách chính xác nhất.

#### Tiêu chí chấp nhận

1. THE Search_Engine SHALL hỗ trợ kết hợp đồng thời tất cả các tham số: `keyword`, `address`, `job_type`, `salary_min`, `salary_max`, `include_negotiable`, `skills`, `sort`, `page`, và `per_page` trong một yêu cầu duy nhất.
2. WHEN nhiều tham số Bộ_lọc được cung cấp cùng lúc, THE Search_Engine SHALL áp dụng tất cả điều kiện lọc theo quan hệ AND (giao của các tập kết quả).
3. WHEN không có Listing nào thỏa mãn tổ hợp Bộ_lọc, THE Search_Engine SHALL trả về danh sách rỗng cùng metadata phân trang với `total = 0` thay vì trả về lỗi.

---

### Requirement 10: Hiệu năng truy vấn

**User Story:** Là một Ứng_viên, tôi muốn kết quả tìm kiếm được trả về nhanh chóng, để tôi không phải chờ đợi lâu khi tìm việc.

#### Tiêu chí chấp nhận

1. WHEN Search_Engine thực hiện tìm kiếm từ khóa, THE Search_Engine SHALL sử dụng FULLTEXT INDEX `ft_listings_title_desc` trên các cột `title` và `predes` của bảng `listings`.
2. WHEN Search_Engine thực hiện lọc theo `address`, `job_type`, hoặc `salary`, THE Search_Engine SHALL tận dụng các INDEX tương ứng (`idx_listings_address`, `idx_listings_job_type`, `idx_listings_salary`) đã có trên bảng `listings`.
3. WHILE hệ thống đang xử lý yêu cầu tìm kiếm với tổng số Listing trong bảng không vượt quá 100.000 bản ghi, THE Search_Engine SHALL trả về Kết_quả_tìm_kiếm trong vòng 2 giây.
