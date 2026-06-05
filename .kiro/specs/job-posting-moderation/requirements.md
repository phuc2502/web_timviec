# Requirements Document

## Introduction

Module này nâng cấp hệ thống đăng tin tuyển dụng hiện tại của ITWorks từ một form đơn giản thành hệ thống quản lý tuyển dụng toàn diện. Hệ thống hỗ trợ Nhà tuyển dụng (NTD) nhập liệu chi tiết, chọn chế độ đăng tin (ngay lập tức / lên lịch / lưu nháp), Admin kiểm duyệt nội dung trước khi công khai, và quản lý vòng đời tin sau khi đăng (tạm dừng, đóng, gia hạn, nhân bản). Hệ thống cũng cung cấp thống kê lượt xem/tương tác và cơ chế phân quyền dựa trên subscription của NTD.

## Glossary

- **Listing**: Tin tuyển dụng được tạo bởi Nhà tuyển dụng trên hệ thống ITWorks.
- **NTD (Nhà tuyển dụng)**: Người dùng có `user_type = 'employer'` đã đăng nhập.
- **Admin**: Người dùng có `is_admin = 1`, có quyền kiểm duyệt và quản lý toàn bộ tin.
- **Ứng viên**: Người dùng có `user_type = 'employee'`.
- **ModerationService**: Service PHP trong Laravel xử lý lọc từ khóa và phân loại trạng thái tự động.
- **Scheduler**: Laravel Task Scheduler chạy qua Cronjob (`php artisan schedule:work`), thực thi tác vụ tự động theo lịch.
- **StoreJobRequest**: Form Request Laravel thực hiện validation đầu vào khi tạo hoặc cập nhật tin.
- **Category**: Danh mục ngành nghề, lưu trong bảng `categories`.
- **Skill**: Kỹ năng/Tag kỹ thuật, lưu trong bảng `skills`.
- **Quota**: Giới hạn số tin đang hoạt động (active) đồng thời mà NTD được phép đăng, xác định bởi gói subscription.
- **AuditLog**: Bản ghi lịch sử thay đổi của tin tuyển dụng, lưu trong bảng `listing_audit_logs`.
- **BannedKeyword**: Từ khóa bị cấm, cấu hình trong `config/moderation.php` hoặc bảng DB dành cho Admin quản lý.
- **Rate_Limit**: Giới hạn tần suất tạo Listing mới trong khoảng thời gian nhất định (24 giờ) để ngăn chặn spam.
- **Soft_Delete**: Xóa logic bằng cách đặt timestamp `deleted_at`, dữ liệu vẫn còn trong database nhưng bị ẩn khỏi các query thông thường.
- **Hard_Delete**: Xóa vĩnh viễn bản ghi khỏi database, không thể khôi phục.

---

## Requirements

---

### Requirement 1: Nhập liệu chi tiết tin tuyển dụng

**User Story:** Là một Nhà tuyển dụng, tôi muốn điền đầy đủ thông tin chi tiết cho tin tuyển dụng, để ứng viên có thể hiểu rõ vị trí và nộp đơn phù hợp.

#### Acceptance Criteria

1. THE Listing SHALL có các trường bắt buộc: tiêu đề công việc (`title`, tối đa 255 ký tự), mô tả chi tiết (`description`), địa chỉ làm việc (`address`), loại công việc (`job_type`), danh mục ngành nghề (`category_id`), và ngày đóng nhận hồ sơ (`application_close_date`).
2. THE Listing SHALL có các trường tùy chọn: cấp bậc (`level`), lương tối thiểu (`salary_min`), lương tối đa (`salary_max`), cờ thỏa thuận (`is_negotiable`), cờ ẩn lương (`hide_salary`), ngày bắt đầu dự kiến (`start_date`), số lượng tuyển dụng (`vacancy_count`), email liên hệ (`contact_email`), số điện thoại liên hệ (`contact_phone`), và file JD đính kèm (`jd_file_path`).
3. WHEN NTD nhập `salary_min` và `salary_max`, THE StoreJobRequest SHALL kiểm tra `salary_min >= 0` và `salary_max >= salary_min`.
4. IF NTD để trống `salary_min` và `salary_max` và không chọn `is_negotiable`, THEN THE StoreJobRequest SHALL trả về lỗi validation yêu cầu NTD xác định thông tin lương. Tin tuyển dụng có `is_negotiable = true` không bắt buộc phải nhập khoảng lương.
5. THE Listing SHALL cho phép NTD gắn từ 1 đến 20 Skill từ bảng `skills` thông qua bảng trung gian `listing_skill`.
6. WHEN NTD nhập tên Skill chưa tồn tại trong bảng `skills`, THE Listing SHALL tự động tạo mới bản ghi Skill với `slug` được sinh từ `name`.
7. THE Listing SHALL cho phép NTD upload file JD định dạng PDF hoặc DOCX, dung lượng tối đa 5MB.
8. IF NTD upload file JD sai định dạng hoặc vượt quá 5MB, THEN THE StoreJobRequest SHALL từ chối và trả về thông báo lỗi cụ thể.
9. WHEN NTD nhập `application_close_date`, THE StoreJobRequest SHALL kiểm tra ngày đó không nhỏ hơn ngày hiện tại.

---

### Requirement 2: Chế độ đăng tin đa dạng

**User Story:** Là một Nhà tuyển dụng, tôi muốn chọn cách thức đăng tin (ngay lập tức, lên lịch, hoặc lưu nháp), để linh hoạt trong việc lên kế hoạch tuyển dụng.

#### Acceptance Criteria

1. THE Listing SHALL hỗ trợ ba chế độ đăng (`publish_mode`): `immediate` (đăng ngay), `scheduled` (lên lịch), và `draft` (lưu nháp).
2. WHEN NTD chọn `publish_mode = 'immediate'` và gửi form, THE Listing SHALL được gửi vào hàng đợi kiểm duyệt với trạng thái `pending_review`.
3. WHEN NTD chọn `publish_mode = 'draft'`, THE Listing SHALL được lưu với trạng thái `draft` và không gửi đến hàng đợi kiểm duyệt.
4. WHEN NTD chọn `publish_mode = 'scheduled'`, THE StoreJobRequest SHALL yêu cầu NTD nhập thời điểm lên lịch (`scheduled_at`) lớn hơn thời điểm hiện tại ít nhất 5 phút.
5. WHEN NTD chọn `publish_mode = 'scheduled'` và form hợp lệ, THE Listing SHALL được lưu với trạng thái `scheduled`.
6. WHILE Listing có trạng thái `scheduled` và thời điểm `scheduled_at` chưa đến, THE Scheduler SHALL giữ nguyên trạng thái `scheduled`.
7. WHEN thời điểm `scheduled_at` đến, THE Scheduler SHALL tự động chuyển trạng thái Listing từ `scheduled` sang `pending_review`.
8. WHILE Listing có trạng thái `draft`, THE NTD SHALL có thể chỉnh sửa và gửi duyệt bất cứ lúc nào bằng cách chuyển `publish_mode` sang `immediate`.

---

### Requirement 3: Kiểm duyệt nội dung tự động và thủ công

**User Story:** Là một Admin, tôi muốn hệ thống lọc từ khóa tự động và có giao diện duyệt tin thủ công, để đảm bảo chất lượng nội dung trên nền tảng.

#### Acceptance Criteria

1. WHEN Listing được gửi vào hàng đợi kiểm duyệt (`pending_review`), THE ModerationService SHALL tự động quét `title` và `description` so với danh sách BannedKeyword.
2. IF ModerationService phát hiện BannedKeyword trong Listing, THEN THE ModerationService SHALL tự động chuyển trạng thái Listing sang `rejected` và ghi lý do `rejection_reason` là danh sách từ khóa vi phạm.
3. IF ModerationService không phát hiện BannedKeyword, THEN THE ModerationService SHALL giữ trạng thái Listing là `pending_review` và hiển thị trong danh sách chờ duyệt của Admin.
4. THE Admin SHALL có thể xem danh sách tất cả Listing có trạng thái `pending_review` với thông tin: tiêu đề, tên công ty NTD, thời điểm nộp, và kết quả kiểm tra từ khóa tự động. Listing đã chuyển sang `rejected` SHALL không hiển thị trong danh sách này.
5. WHEN Admin duyệt một Listing (`pending_review` → `active`), THE ModerationService SHALL cập nhật trạng thái Listing thành `active` và gửi thông báo email cho NTD. IF dịch vụ gửi email gặp lỗi, THEN THE ModerationService SHALL giữ nguyên trạng thái `active` của Listing và ghi lỗi email vào Laravel log.
6. WHEN Admin từ chối một Listing, THE Admin SHALL bắt buộc nhập lý do từ chối (`rejection_reason`), sau đó THE ModerationService SHALL cập nhật trạng thái Listing thành `rejected` và gửi thông báo email cho NTD kèm lý do.
7. THE Admin SHALL có thể chỉnh sửa danh sách BannedKeyword thông qua giao diện quản trị hoặc file `config/moderation.php` mà không cần triển khai lại ứng dụng.
8. WHEN Admin cập nhật danh sách BannedKeyword trong DB, THE ModerationService SHALL sử dụng danh sách mới ngay ở lần kiểm duyệt tiếp theo.

---

### Requirement 4: Vòng đời trạng thái tin tuyển dụng

**User Story:** Là một Nhà tuyển dụng, tôi muốn quản lý tin sau khi đăng (tạm dừng, đóng, gia hạn, nhân bản), để điều chỉnh kế hoạch tuyển dụng linh hoạt theo nhu cầu.

#### Acceptance Criteria

1. THE Listing SHALL vận hành theo state machine với các trạng thái: `draft`, `pending_review`, `scheduled`, `active`, `paused`, `closed`, `rejected`, `expired`, `archived`.
2. WHEN NTD tạm dừng Listing đang `active`, THE Listing SHALL chuyển sang trạng thái `paused` và không hiển thị với Ứng viên.
3. WHEN NTD tiếp tục Listing đang `paused`, THE Listing SHALL chuyển về trạng thái `active` và hiển thị lại với Ứng viên.
4. WHEN NTD đóng Listing đang `active`, `paused`, `scheduled`, hoặc `draft`, THE Listing SHALL chuyển sang trạng thái `closed` và không nhận thêm đơn ứng tuyển.
5. WHEN `application_close_date` của Listing đang `active` nhỏ hơn ngày hiện tại (`CURDATE()`), THE Scheduler SHALL chạy hàng ngày vào lúc 23:59 để chuyển trạng thái Listing sang `expired`. IF Scheduler không chạy thành công vào đúng 23:59, THEN THE Listing SHALL chỉ được chuyển sang `expired` ở lần Scheduler chạy thành công tiếp theo.
6. WHEN NTD gia hạn Listing đang `expired` hoặc `closed`, THE Listing SHALL yêu cầu NTD nhập `application_close_date` mới lớn hơn ngày hiện tại, sau đó chuyển trạng thái về `pending_review`.
7. WHEN NTD nhân bản (clone) một Listing bất kỳ, THE Listing SHALL tạo bản sao mới với `publish_mode = 'draft'`, trạng thái `draft`, sao chép toàn bộ nội dung trừ `application_close_date` và `scheduled_at`.
8. WHEN Listing đang `rejected` đã quá 30 ngày kể từ ngày từ chối, THE Scheduler SHALL tự động chuyển Listing sang trạng thái `archived`.
9. IF NTD cố thực hiện chuyển đổi trạng thái không hợp lệ (ví dụ: từ `draft` sang `closed`), THEN THE Listing SHALL từ chối yêu cầu và trả về lỗi HTTP 422.

---

### Requirement 5: Kiểm duyệt lại khi chỉnh sửa tin đang Active

**User Story:** Là một Admin, tôi muốn được thông báo và duyệt lại khi NTD sửa nội dung quan trọng của tin đang hiển thị, để đảm bảo tin công khai luôn đạt tiêu chuẩn chất lượng.

#### Acceptance Criteria

1. WHEN NTD chỉnh sửa `title` hoặc `description` của Listing đang `active`, THE Listing SHALL tự động chuyển về trạng thái `pending_review` và tạm ẩn khỏi kết quả tìm kiếm của Ứng viên, ngay cả khi cùng lúc có sửa thêm các trường không quan trọng khác.
2. WHEN NTD chỉnh sửa các trường không quan trọng (lương, thông tin liên hệ, `vacancy_count`, `application_close_date`, Skill) của Listing đang `active`, THE Listing SHALL giữ nguyên trạng thái `active` và hiển thị thay đổi ngay lập tức.
3. WHEN Listing được chuyển về `pending_review` do chỉnh sửa, THE ModerationService SHALL tự động quét lại từ khóa trước khi chuyển vào hàng đợi thủ công của Admin.
4. THE AuditLog SHALL ghi lại mọi lần chỉnh sửa Listing với: `user_id` người thực hiện, danh sách trường thay đổi, giá trị cũ (`old_values` JSON), giá trị mới (`new_values` JSON), và timestamp.
5. THE Admin SHALL có thể xem lịch sử AuditLog của bất kỳ Listing nào.

---

### Requirement 6: Phân quyền và Quota đăng tin

**User Story:** Là một Admin hệ thống, tôi muốn giới hạn số tin đăng theo gói subscription của NTD, để đảm bảo công bằng và thúc đẩy nâng cấp gói.

#### Acceptance Criteria

1. WHILE NTD có `status = 'paid'` hoặc trong giai đoạn `user_trial` chưa hết hạn, THE Listing SHALL cho phép NTD tạo và đăng tin tuyển dụng.
2. IF NTD có `status = 'unpaid'` và thời điểm hiện tại vượt quá `user_trial`, THEN THE Listing SHALL từ chối yêu cầu tạo tin và chuyển hướng NTD đến trang `/subscribe`.
3. WHILE NTD có `plan = 'monthly'`, THE Quota SHALL giới hạn tối đa 5 Listing có trạng thái `active` hoặc `pending_review` hoặc `scheduled` tại cùng một thời điểm.
4. WHILE NTD có `plan = 'yearly'`, THE Quota SHALL giới hạn tối đa 15 Listing có trạng thái `active` hoặc `pending_review` hoặc `scheduled` tại cùng một thời điểm.
5. IF NTD đã đạt Quota, THEN THE Listing SHALL từ chối yêu cầu tạo tin mới và hiển thị thông báo hướng dẫn NTD nâng cấp gói hoặc đóng bớt tin cũ. WHILE người dùng là Admin, THE Listing SHALL hiển thị thông báo Quota tương tự khi NTD thông thường gặp giới hạn nhưng vẫn cho phép Admin thực hiện thao tác.
6. THE Admin SHALL không bị giới hạn bởi Quota và có thể tạo, sửa, xóa Listing của bất kỳ NTD nào.
7. WHEN NTD bị Admin ban (`is_banned = 1`), THE Listing SHALL tự động thực thi logic tạm dừng và chuyển tất cả Listing `active` và `pending_review` của NTD đó sang `paused`, bất kể NTD đó có bao nhiêu Listing đang hoạt động.

---

### Requirement 7: Báo cáo tin vi phạm

**User Story:** Là một Ứng viên, tôi muốn báo cáo tin tuyển dụng có nội dung vi phạm, để giúp nền tảng duy trì chất lượng.

#### Acceptance Criteria

1. WHEN Ứng viên đã đăng nhập xem một Listing đang `active`, THE Listing SHALL hiển thị nút "Báo cáo vi phạm".
2. WHEN Ứng viên nhấn báo cáo, THE Listing SHALL yêu cầu Ứng viên chọn lý do báo cáo từ danh sách định sẵn (tin giả, lừa đảo, nội dung không phù hợp, thông tin sai lệch) và cho phép nhập mô tả thêm tùy chọn.
3. THE Listing SHALL lưu báo cáo vào bảng `listing_reports` với `listing_id`, `user_id` Ứng viên, lý do, mô tả, và trạng thái `pending`.
4. IF Ứng viên đã báo cáo Listing này trước đó, THEN THE Listing SHALL từ chối báo cáo trùng lặp và thông báo cho Ứng viên.
5. WHEN một Listing nhận đủ 5 báo cáo có trạng thái `pending` (ngưỡng chính xác là 5, tính cả báo cáo thứ 5), THE ModerationService SHALL tự động chuyển Listing sang `paused` và luôn luôn thêm vào hàng đợi ưu tiên xem xét của Admin.
6. THE Admin SHALL có thể xem danh sách tất cả báo cáo, phân loại theo trạng thái (`pending`, `reviewed`, `dismissed`), và thực hiện hành động tương ứng trên Listing bị báo cáo.

---

### Requirement 8: Thống kê và Analytics

**User Story:** Là một Nhà tuyển dụng, tôi muốn xem thống kê lượt xem và tương tác của từng tin tuyển dụng, để đánh giá hiệu quả và tối ưu hóa nội dung.

#### Acceptance Criteria

1. WHEN Ứng viên xem chi tiết Listing đang `active`, THE Listing SHALL ghi một bản ghi vào bảng `listing_views` với: `listing_id`, `user_id` (nếu đã đăng nhập, hoặc NULL nếu khách), `ip_address`, `traffic_source` (referrer), `action_type = 'view'`, và timestamp.
2. WHEN Ứng viên nhấn nút "Ứng tuyển" trên Listing, THE Listing SHALL ghi một bản ghi vào `listing_views` với `action_type = 'apply_click'`.
3. THE NTD SHALL có thể xem trang Analytics của từng Listing thuộc sở hữu của NTD đó với dữ liệu: tổng lượt xem, lượt xem theo ngày (7 ngày gần nhất), tổng lượt click ứng tuyển, và tỷ lệ chuyển đổi (apply_click / view).
4. IF NTD cố truy cập trang Analytics của Listing không thuộc sở hữu của mình, THEN THE Listing SHALL từ chối yêu cầu và trả về lỗi HTTP 403.
5. WHILE NTD xem trang Analytics, THE Listing SHALL hiển thị biểu đồ đường (line chart) bằng Chart.js thể hiện xu hướng lượt xem theo ngày.
6. THE Admin SHALL có thể xem trang Analytics của bất kỳ Listing nào với đầy đủ dữ liệu tương tự NTD.
7. THE Admin SHALL có thể xem thống kê tổng hợp toàn hệ thống: tổng tin đăng mới theo tuần, tổng lượt xem trên tất cả Listing, số tin đang chờ duyệt, và tỷ lệ chuyển đổi trung bình.

---

### Requirement 9: Thông báo email theo sự kiện

**User Story:** Là một Nhà tuyển dụng, tôi muốn nhận email thông báo về trạng thái tin của mình, để theo dõi kịp thời mà không cần đăng nhập liên tục.

#### Acceptance Criteria

1. WHEN Listing của NTD được Admin duyệt và chuyển sang `active`, THE Listing SHALL gửi email thông báo cho NTD với tiêu đề và đường dẫn đến tin đăng.
2. WHEN Listing của NTD bị Admin từ chối (`rejected`), THE Listing SHALL gửi email thông báo cho NTD kèm `rejection_reason` và gợi ý chỉnh sửa.
3. WHEN Listing đang `active` còn 3 ngày trước khi đến `application_close_date`, THE Scheduler SHALL gửi email nhắc nhở NTD gia hạn hoặc đóng tin.
4. WHEN Ứng viên nộp đơn vào Listing của NTD, THE Listing SHALL gửi email thông báo cho NTD với tên ứng viên và đường dẫn xem hồ sơ, kể cả trong trường hợp Admin đang xử lý từ chối Listing cùng lúc.
5. WHERE NTD có `email_notify = 0`, THE Listing SHALL không gửi bất kỳ email thông báo nào cho NTD đó, bao gồm cả email duyệt tin, email từ chối, email nhắc hết hạn, và email thông báo ứng viên mới.

---

### Requirement 10: Tự động hóa vòng đời qua Scheduler

**User Story:** Là một Admin hệ thống, tôi muốn hệ thống tự động xử lý các tác vụ định kỳ, để giảm thiểu can thiệp thủ công và đảm bảo trạng thái tin luôn chính xác.

#### Acceptance Criteria

1. THE Scheduler SHALL chạy tác vụ kiểm tra Listing `scheduled` mỗi phút một lần và chuyển các Listing có `scheduled_at <= NOW()` sang `pending_review`.
2. THE Scheduler SHALL chạy tác vụ kiểm tra hạn hồ sơ mỗi ngày một lần vào 00:00, chuyển Listing `active` có `application_close_date < CURDATE()` sang `expired`.
3. THE Scheduler SHALL chạy tác vụ lưu trữ mỗi ngày một lần vào 01:00, chuyển Listing `rejected` có ngày từ chối cách hiện tại hơn 30 ngày sang `archived`.
4. IF một tác vụ Scheduler gặp lỗi trong quá trình xử lý, THEN THE Scheduler SHALL ghi lỗi vào Laravel log (`storage/logs/laravel.log`) và tiếp tục xử lý các Listing còn lại trong cùng batch.
5. THE Scheduler SHALL được kích hoạt trên môi trường XAMPP local bằng lệnh `php artisan schedule:work` chạy trong terminal riêng.

---

### Requirement 11: Quản lý danh mục ngành nghề (Category)

**User Story:** Là một Admin, tôi muốn quản lý danh mục ngành nghề, để NTD có thể phân loại tin đăng chính xác và ứng viên tìm kiếm dễ dàng hơn.

#### Acceptance Criteria

1. THE Admin SHALL có thể thêm, sửa, xóa Category với các trường: `name` và `slug` (tự động sinh từ `name`).
2. IF Admin xóa Category đang được gán cho ít nhất một Listing, THEN THE Category SHALL từ chối yêu cầu xóa và hiển thị thông báo lỗi yêu cầu Admin gán lại Category cho các Listing liên quan trước khi xóa.
3. WHEN NTD tạo hoặc sửa Listing, THE Listing SHALL hiển thị dropdown chọn Category từ danh sách Category đang hoạt động trong bảng `categories`.
4. THE Category SHALL hỗ trợ tìm kiếm autocomplete trên form đăng tin, trả về kết quả trong vòng 500ms.

---

### Requirement 12: Tìm kiếm và lọc tin tuyển dụng

**User Story:** Là một Ứng viên, tôi muốn tìm kiếm và lọc tin tuyển dụng theo nhiều tiêu chí, để nhanh chóng tìm được công việc phù hợp.

#### Acceptance Criteria

1. THE Listing SHALL chỉ hiển thị các tin có trạng thái `active` trong kết quả tìm kiếm công khai.
2. WHEN Ứng viên nhập từ khóa tìm kiếm, THE Listing SHALL tìm kiếm full-text trên các trường `title` và `description` sử dụng FULLTEXT index.
3. THE Listing SHALL hỗ trợ lọc kết quả theo: `category_id`, `job_type`, `address` (tỉnh/thành), khoảng lương (`salary_min` đến `salary_max`), và danh sách Skill.
4. WHEN Ứng viên áp dụng bộ lọc hoặc không áp dụng bộ lọc, THE Listing SHALL trả về kết quả được phân trang 20 tin mỗi trang và hoàn thành trong vòng 1 giây.
5. THE Listing SHALL sắp xếp kết quả tìm kiếm mặc định theo thứ tự `created_at` giảm dần (tin mới nhất lên đầu).

---

### Requirement 13: Xóa vĩnh viễn Listing (Hard Delete)

**User Story:** Là một Admin, tôi muốn có khả năng xóa vĩnh viễn các tin tuyển dụng không còn giá trị, để dọn dẹp cơ sở dữ liệu và loại bỏ nội dung không phù hợp.

#### Acceptance Criteria

1. THE Admin SHALL có thể xóa vĩnh viễn (hard delete) Listing khỏi cơ sở dữ liệu.
2. WHEN Admin thực hiện hard delete Listing, THE Listing SHALL kiểm tra Listing đó phải ở trạng thái `archived` hoặc `rejected` và đã tồn tại trong trạng thái đó ít nhất 90 ngày.
3. IF Listing không đáp ứng điều kiện ở acceptance criterion 2, THEN THE Listing SHALL từ chối yêu cầu xóa và trả về lỗi HTTP 422 với thông báo rõ ràng về điều kiện bắt buộc.
4. WHEN Admin xác nhận hard delete, THE Listing SHALL xóa vĩnh viễn bản ghi Listing và tất cả dữ liệu liên quan trong các bảng: `listing_skill`, `listing_views`, `listing_reports`, trừ bảng `listing_audit_logs`.
5. THE Listing SHALL giữ lại toàn bộ bản ghi trong `listing_audit_logs` ngay cả sau khi hard delete, để phục vụ mục đích kiểm toán và tuân thủ quy định.
6. THE NTD SHALL không có quyền xóa vĩnh viễn bất kỳ Listing nào, kể cả Listing thuộc sở hữu của chính họ.
7. WHEN NTD muốn gỡ bỏ tin tuyển dụng, THE NTD SHALL chỉ có thể chuyển Listing sang trạng thái `closed` hoặc để hệ thống tự động archive sau 30 ngày khi Listing ở trạng thái `rejected`.
8. WHEN Admin thực hiện hard delete, THE Listing SHALL ghi lại hành động vào `listing_audit_logs` với `action = 'hard_deleted'`, `user_id` của Admin, và timestamp trước khi xóa bản ghi chính.

---

### Requirement 14: Rate Limiting cho việc tạo Listing

**User Story:** Là một Admin hệ thống, tôi muốn giới hạn tần suất tạo tin tuyển dụng của NTD, để ngăn chặn spam và lạm dụng hệ thống.

#### Acceptance Criteria

1. WHILE NTD có `plan = 'monthly'` hoặc `plan = 'yearly'`, THE Listing SHALL giới hạn tối đa 5 Listing mới được tạo trong vòng 24 giờ.
2. WHILE NTD có `status = 'paid'` với `plan = 'trial'` hoặc đang trong giai đoạn `user_trial`, THE Listing SHALL giới hạn tối đa 2 Listing mới được tạo trong vòng 24 giờ.
3. WHEN NTD vượt quá giới hạn rate limit trong acceptance criterion 1 hoặc 2, THE Listing SHALL từ chối yêu cầu tạo Listing mới và trả về lỗi HTTP 429 với thông báo rõ ràng về số lượng còn lại và thời gian reset.
4. THE Listing SHALL tính rate limit dựa trên số lượng Listing được tạo (action `created`) trong 24 giờ qua, bất kể trạng thái hiện tại của các Listing đó (`draft`, `pending_review`, `active`, `rejected`, v.v.).
5. WHEN NTD thực hiện repost (nhân bản) Listing, THE Listing SHALL cũng áp dụng rate limit như khi tạo Listing mới.
6. THE Admin SHALL không bị giới hạn bởi rate limit và có thể tạo số lượng Listing không giới hạn.
7. THE Listing SHALL lưu thông tin rate limit vào cache (Redis hoặc file cache) với key dạng `rate_limit:listing:create:{user_id}` và TTL là 24 giờ.
8. WHEN thời gian 24 giờ đã trôi qua kể từ Listing đầu tiên được tạo trong chu kỳ, THE Listing SHALL tự động reset bộ đếm rate limit cho NTD đó.

---

### Requirement 15: Xử lý Listing khi NTD bị xóa tài khoản

**User Story:** Là một Admin hệ thống, tôi muốn đảm bảo dữ liệu Listing được xử lý nhất quán khi tài khoản NTD bị xóa, để duy trì tính toàn vẹn dữ liệu và tuân thủ quy định.

#### Acceptance Criteria

1. WHEN Admin thực hiện soft delete tài khoản NTD (đặt `deleted_at` trong bảng `users`), THE Listing SHALL tự động chuyển tất cả Listing có trạng thái `active`, `pending_review`, hoặc `scheduled` của NTD đó sang trạng thái `paused`.
2. WHEN Admin thực hiện soft delete tài khoản NTD, THE Listing SHALL giữ nguyên trạng thái của các Listing đã ở trạng thái `paused`, `closed`, `rejected`, `expired`, hoặc `archived`.
3. WHEN Admin thực hiện hard delete tài khoản NTD (xóa vĩnh viễn bản ghi khỏi bảng `users`), THE Listing SHALL tự động chuyển tất cả Listing của NTD đó sang trạng thái `archived` với cờ đặc biệt `archived_reason = 'user_deleted'`.
4. WHEN Admin hard delete tài khoản NTD, THE Listing SHALL giữ lại toàn bộ dữ liệu Listing trong bảng `listings`, không xóa vĩnh viễn.
5. THE Listing SHALL giữ lại toàn bộ dữ liệu analytics trong bảng `listing_views` và lịch sử thay đổi trong bảng `listing_audit_logs` cho tất cả Listing của NTD bị xóa, để phục vụ mục đích kiểm toán và phân tích.
6. WHEN NTD bị soft delete và sau đó được khôi phục tài khoản (xóa `deleted_at`), THE Listing SHALL không tự động khôi phục trạng thái cũ của các Listing, và NTD phải tự chuyển các Listing từ `paused` về `active` nếu muốn.
7. THE Listing SHALL ghi lại hành động chuyển đổi trạng thái do xóa tài khoản vào `listing_audit_logs` với `action = 'auto_paused_user_deleted'` hoặc `action = 'auto_archived_user_deleted'` và `user_id` của Admin thực hiện xóa.
8. IF Admin hard delete NTD có hơn 100 Listing, THEN THE Listing SHALL xử lý việc archive theo batch để tránh timeout, với mỗi batch xử lý tối đa 50 Listing.
