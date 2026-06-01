# Requirements Document

## Introduction

Module này cho phép người dùng (ứng viên và khách vãng lai) tìm kiếm và lọc tin tuyển dụng IT trên website web_timviec. Hệ thống tận dụng FULLTEXT INDEX trên bảng `listings` để tìm kiếm theo từ khóa, kết hợp với các bộ lọc đa chiều (loại công việc, hình thức làm việc, kỹ năng, địa điểm, thành phố, mức lương, kinh nghiệm, cấp bậc, quy mô công ty) nhằm trả về danh sách tin tuyển dụng phù hợp, được phân trang và sắp xếp linh hoạt.

**DB Migration cần thiết:**
- Thêm cột `work_mode` ENUM(`'onsite'`,`'remote'`,`'hybrid'`) NOT NULL DEFAULT `'onsite'` vào bảng `listings`, kèm index `idx_listings_work_mode`.
- Thêm cột `experience_years_min` TINYINT UNSIGNED NULL vào bảng `listings`, kèm index `idx_listings_exp_min`.
- Thêm cột `experience_years_max` TINYINT UNSIGNED NULL vào bảng `listings`, kèm index `idx_listings_exp_max`.
- Thêm cột `job_level` ENUM(`'intern'`,`'fresher'`,`'junior'`,`'middle'`,`'senior'`,`'lead'`,`'manager'`) NULL vào bảng `listings`, kèm index `idx_listings_job_level`.
- Cột `job_type` trong bảng `listings` cần cập nhật ENUM thành: `'full-time'`, `'part-time'`, `'freelance'`, `'internship'` (loại bỏ `'remote'` và `'hybrid'` — chuyển sang `work_mode`).
- Cột `salary` hiện tại (INT UNSIGNED, 0 = thỏa thuận) được giữ nguyên. Trong tương lai có thể cần tách thành `salary_min`/`salary_max` ở DB level để hỗ trợ khoảng lương chính xác hơn.

---

## Glossary

- **Search_Engine**: Thành phần xử lý truy vấn tìm kiếm và lọc tin tuyển dụng trong hệ thống.
- **Listing**: Một tin tuyển dụng trong bảng `listings`, có trạng thái `open`, `hidden`, hoặc `closed`.
- **Active_Listing**: Tin tuyển dụng có `status = 'open'` và `application_close_date >= ngày hiện tại`.
- **Keyword**: Chuỗi văn bản người dùng nhập vào ô tìm kiếm, dùng để khớp với `title` và `predes` của Listing.
- **Filter**: Tham số lọc bổ sung bao gồm: `job_type`, `work_mode`, `skills`, `skill_mode`, `address`, `city`, `salary_min`, `salary_max`, `exp_min`, `exp_max`, `job_level`, `company_size`.
- **Skill**: Kỹ năng kỹ thuật trong bảng `skills`, liên kết với Listing qua bảng pivot `listing_skill`.
- **Skill_Mode**: Chế độ lọc kỹ năng — `and` yêu cầu Listing có tất cả kỹ năng được chọn; `or` yêu cầu Listing có ít nhất một kỹ năng được chọn.
- **Work_Mode**: Hình thức làm việc của Listing — `onsite` (tại văn phòng), `remote` (làm từ xa), `hybrid` (kết hợp).
- **Job_Level**: Cấp bậc công việc của Listing — `intern`, `fresher`, `junior`, `middle`, `senior`, `lead`, `manager`.
- **City_Filter**: Tham số `city` dùng để lọc Listing theo tỉnh/thành phố chuẩn hóa, lấy từ các giá trị distinct trong cột `address`, tìm kiếm bằng LIKE không phân biệt hoa thường.
- **Employer**: Người dùng có `user_type = 'employer'`, sở hữu các Listing.
- **Result_Set**: Tập hợp các Listing thỏa mãn điều kiện tìm kiếm và lọc, được phân trang.
- **Relevance_Score**: Điểm liên quan được tính bởi MySQL FULLTEXT MATCH...AGAINST, dùng để sắp xếp kết quả.
- **Search_Request**: Đối tượng chứa toàn bộ tham số tìm kiếm và lọc từ người dùng.

---

## Requirements

### Requirement 1: Tìm kiếm theo từ khóa

**User Story:** Là một ứng viên IT, tôi muốn tìm kiếm tin tuyển dụng bằng từ khóa (tên vị trí, mô tả), để tôi có thể nhanh chóng tìm được công việc phù hợp với mình.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có Keyword không rỗng, THE Search_Engine SHALL thực hiện truy vấn FULLTEXT MATCH...AGAINST trên các cột `title` và `predes` của bảng `listings`.
2. WHEN người dùng gửi Search_Request có Keyword không rỗng, THE Search_Engine SHALL chỉ trả về các Active_Listing có Relevance_Score lớn hơn 0.
3. WHEN người dùng gửi Search_Request có Keyword rỗng hoặc không có Keyword, THE Search_Engine SHALL trả về toàn bộ Active_Listing không áp dụng điều kiện FULLTEXT.
4. WHEN người dùng gửi Search_Request có Keyword không rỗng, THE Search_Engine SHALL sắp xếp Result_Set theo Relevance_Score giảm dần theo mặc định.
5. IF Keyword chứa ký tự đặc biệt có thể gây lỗi SQL injection, THEN THE Search_Engine SHALL làm sạch Keyword trước khi đưa vào truy vấn.

---

### Requirement 2: Lọc theo loại công việc (Job Type)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo hình thức hợp đồng (full-time, part-time, v.v.), để tôi chỉ thấy những tin phù hợp với nhu cầu của mình.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `job_type` hợp lệ, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có `job_type` khớp với giá trị được cung cấp (so sánh không phân biệt hoa thường).
2. THE Search_Engine SHALL chấp nhận các giá trị `job_type` sau: `full-time`, `part-time`, `freelance`, `internship`.
3. IF người dùng cung cấp giá trị `job_type` không nằm trong danh sách hợp lệ, THEN THE Search_Engine SHALL bỏ qua tham số `job_type` đó, không áp dụng bộ lọc, và trả về Result_Set như thể `job_type` không được cung cấp.
4. WHEN người dùng không cung cấp tham số `job_type`, THE Search_Engine SHALL không áp dụng bộ lọc loại công việc.

---

### Requirement 3: Lọc theo hình thức làm việc (Work Mode)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo hình thức làm việc (onsite, remote, hybrid), để tôi chỉ thấy những tin phù hợp với lịch trình và địa điểm của mình.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `work_mode` hợp lệ, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có `work_mode` khớp với giá trị được cung cấp (so sánh không phân biệt hoa thường).
2. THE Search_Engine SHALL chấp nhận các giá trị `work_mode` sau: `onsite`, `remote`, `hybrid`.
3. IF người dùng cung cấp giá trị `work_mode` không nằm trong danh sách hợp lệ, THEN THE Search_Engine SHALL bỏ qua tham số `work_mode` đó, không áp dụng bộ lọc, và trả về Result_Set như thể `work_mode` không được cung cấp.
4. WHEN người dùng không cung cấp tham số `work_mode`, THE Search_Engine SHALL không áp dụng bộ lọc hình thức làm việc.

---

### Requirement 4: Lọc theo kỹ năng (Skills)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo kỹ năng kỹ thuật yêu cầu (ví dụ: Laravel, React, Docker), để tôi tìm được công việc phù hợp với stack công nghệ của mình.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có một hoặc nhiều `skill_id` hợp lệ và `skill_mode = 'and'`, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có liên kết với tất cả các Skill được chỉ định qua bảng `listing_skill`.
2. WHEN người dùng gửi Search_Request có một hoặc nhiều `skill_id` hợp lệ và `skill_mode = 'or'`, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có liên kết với ít nhất một trong các Skill được chỉ định qua bảng `listing_skill`.
3. WHEN người dùng không cung cấp tham số `skill_mode`, THE Search_Engine SHALL áp dụng Skill_Mode mặc định là `and`.
4. IF người dùng cung cấp `skill_id` không tồn tại trong bảng `skills`, THEN THE Search_Engine SHALL bỏ qua `skill_id` không hợp lệ đó.
5. IF người dùng cung cấp mảng `skills` có nhiều hơn 15 phần tử, THEN THE Search_Engine SHALL chỉ sử dụng 15 phần tử đầu tiên và bỏ qua các phần tử còn lại mà không báo lỗi.
6. WHEN người dùng không cung cấp tham số `skills`, THE Search_Engine SHALL không áp dụng bộ lọc kỹ năng.
7. THE Search_Engine SHALL trả về danh sách tất cả Skill hiện có để hiển thị trên giao diện bộ lọc.

---

### Requirement 5: Lọc theo địa điểm (Address / City)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo địa điểm làm việc, để tôi tìm được công việc gần nơi tôi sinh sống.

> **Ghi chú kỹ thuật:** Bộ lọc city hiện dựa trên LIKE trên cột `address` tự do — có nguy cơ không nhất quán nếu employer nhập địa chỉ theo nhiều định dạng khác nhau. Về lâu dài nên cân nhắc thêm cột `city` chuẩn hóa riêng vào bảng `listings`.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `address` không rỗng và không chỉ chứa khoảng trắng, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có cột `address` chứa chuỗi `address` được cung cấp (LIKE không phân biệt hoa thường; các ký tự `%` và `_` trong tham số phải được escape trước khi đưa vào truy vấn).
2. WHEN người dùng gửi Search_Request có tham số `city` không rỗng và không chỉ chứa khoảng trắng, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có cột `address` chứa chuỗi `city` được cung cấp (LIKE không phân biệt hoa thường; các ký tự `%` và `_` phải được escape).
3. WHEN người dùng cung cấp cả tham số `address` và `city` trong cùng một Search_Request, THE Search_Engine SHALL áp dụng logic AND — chỉ trả về Active_Listing thỏa mãn đồng thời cả hai điều kiện `address` và `city`.
4. THE Search_Engine SHALL cung cấp endpoint trả về danh sách các giá trị `city` distinct lấy từ cột `address` của các Active_Listing, để hiển thị trên giao diện bộ lọc thành phố.
5. IF tham số `address` hoặc `city` có độ dài vượt quá 255 ký tự, THEN THE Search_Engine SHALL cắt bớt xuống còn 255 ký tự trước khi thực hiện truy vấn.
6. WHEN người dùng không cung cấp tham số `address` và không cung cấp tham số `city`, THE Search_Engine SHALL không áp dụng bộ lọc địa điểm.

---

### Requirement 6: Lọc theo mức lương (Salary Range)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo khoảng mức lương mong muốn, để tôi chỉ xem những tin có mức lương phù hợp với kỳ vọng của mình.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `salary_min` là số nguyên dương (> 0), THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có `salary >= salary_min` VÀ `salary > 0` (loại trừ tin thỏa thuận khi bộ lọc lương được áp dụng).
2. WHEN người dùng gửi Search_Request có tham số `salary_max` là số nguyên dương (> 0), THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có `salary <= salary_max` VÀ `salary > 0` (loại trừ tin thỏa thuận khi bộ lọc lương được áp dụng).
3. WHEN người dùng cung cấp cả `salary_min` và `salary_max`, THE Search_Engine SHALL áp dụng đồng thời cả hai điều kiện lọc trên cột `salary` của bảng `listings`.
4. IF `salary_min` lớn hơn `salary_max`, THEN THE Search_Engine SHALL trả về lỗi validation HTTP 422 với thông báo: "Mức lương tối thiểu không được lớn hơn mức lương tối đa".
5. WHEN người dùng không cung cấp tham số lương, HOẶC cung cấp `salary_min = 0` mà không cung cấp `salary_max`, HOẶC cung cấp `salary_max = 0` (bất kể `salary_min`), THE Search_Engine SHALL không áp dụng bộ lọc mức lương và bao gồm cả các Listing có `salary = 0` (thỏa thuận) trong kết quả. WHEN người dùng cung cấp `salary_min = 0` kết hợp với `salary_max > 0`, THE Search_Engine SHALL chỉ áp dụng điều kiện `salary <= salary_max AND salary > 0` (bỏ qua điều kiện salary_min).

---

### Requirement 7: Lọc theo kinh nghiệm (Experience)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo số năm kinh nghiệm yêu cầu, để tôi chỉ xem những tin phù hợp với mức kinh nghiệm hiện tại của mình.

> **DB Migration:** Cần thêm cột `experience_years_min` TINYINT UNSIGNED NULL và `experience_years_max` TINYINT UNSIGNED NULL vào bảng `listings`, kèm index `idx_listings_exp_min` và `idx_listings_exp_max`.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `exp_min` là số nguyên không âm, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing thỏa mãn: (`experience_years_max >= exp_min` HOẶC `experience_years_max` là NULL) — tức là khoảng kinh nghiệm của tin có giao nhau với khoảng người dùng yêu cầu.
2. WHEN người dùng gửi Search_Request có tham số `exp_max` là số nguyên không âm, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing thỏa mãn: (`experience_years_min <= exp_max` HOẶC `experience_years_min` là NULL) — tức là khoảng kinh nghiệm của tin có giao nhau với khoảng người dùng yêu cầu.
3. WHEN người dùng cung cấp cả `exp_min` và `exp_max`, THE Search_Engine SHALL áp dụng đồng thời cả hai điều kiện giao nhau (intersection logic), bao gồm các Listing có khoảng kinh nghiệm chồng lấp với khoảng `[exp_min, exp_max]`.
4. IF `exp_min` lớn hơn `exp_max`, THEN THE Search_Engine SHALL trả về lỗi validation HTTP 422 với thông báo: "Kinh nghiệm tối thiểu không được lớn hơn kinh nghiệm tối đa".
5. WHEN người dùng không cung cấp tham số kinh nghiệm, THE Search_Engine SHALL không áp dụng bộ lọc kinh nghiệm và bao gồm cả các Listing có `experience_years_min` và `experience_years_max` là NULL.

---

### Requirement 8: Lọc theo cấp bậc (Job Level)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo cấp bậc công việc, để tôi tìm được vị trí phù hợp với trình độ hiện tại của mình.

> **DB Migration:** Cần thêm cột `job_level` ENUM(`'intern'`,`'fresher'`,`'junior'`,`'middle'`,`'senior'`,`'lead'`,`'manager'`) NULL vào bảng `listings`, kèm index `idx_listings_job_level`.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `job_level` hợp lệ, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing có `job_level` khớp với giá trị được cung cấp (so sánh không phân biệt hoa thường).
2. THE Search_Engine SHALL chấp nhận các giá trị `job_level` sau: `intern`, `fresher`, `junior`, `middle`, `senior`, `lead`, `manager`.
3. IF người dùng cung cấp giá trị `job_level` không nằm trong danh sách hợp lệ, THEN THE Search_Engine SHALL bỏ qua tham số `job_level` đó, không áp dụng bộ lọc, và trả về Result_Set như thể `job_level` không được cung cấp.
4. WHEN người dùng không cung cấp tham số `job_level`, THE Search_Engine SHALL không áp dụng bộ lọc cấp bậc và bao gồm cả các Listing có `job_level` là NULL.

---

### Requirement 9: Lọc theo quy mô công ty (Company Size)

**User Story:** Là một ứng viên IT, tôi muốn lọc tin tuyển dụng theo quy mô công ty, để tôi tìm được môi trường làm việc phù hợp với sở thích của mình.

#### Acceptance Criteria

1. WHEN người dùng gửi Search_Request có tham số `company_size` hợp lệ, THE Search_Engine SHALL lọc Result_Set chỉ bao gồm các Active_Listing thuộc về Employer có `company_size` khớp với giá trị được cung cấp (so sánh không phân biệt hoa thường).
2. THE Search_Engine SHALL chấp nhận các giá trị `company_size` sau: `1-9`, `10-49`, `50-199`, `200-499`, `500+`.
3. IF người dùng cung cấp giá trị `company_size` không nằm trong danh sách hợp lệ, THEN THE Search_Engine SHALL bỏ qua tham số `company_size` đó, không áp dụng bộ lọc, và trả về Result_Set như thể `company_size` không được cung cấp.
4. WHEN người dùng không cung cấp tham số `company_size`, THE Search_Engine SHALL không áp dụng bộ lọc quy mô công ty.

---

### Requirement 10: Phân trang kết quả (Pagination)

**User Story:** Là một ứng viên IT, tôi muốn kết quả tìm kiếm được phân trang, để tôi không bị quá tải thông tin và có thể duyệt qua nhiều trang kết quả.

#### Acceptance Criteria

1. THE Search_Engine SHALL phân trang Result_Set với số lượng mặc định là 15 Listing mỗi trang.
2. WHEN người dùng cung cấp tham số `per_page` là số nguyên từ 5 đến 50, THE Search_Engine SHALL sử dụng giá trị đó làm số lượng Listing mỗi trang.
3. IF người dùng cung cấp `per_page` ngoài khoảng từ 5 đến 50 hoặc không phải số nguyên dương, THEN THE Search_Engine SHALL sử dụng giá trị mặc định là 15.
4. THE Search_Engine SHALL trả về trong response: tổng số kết quả (`total`), số trang hiện tại (`current_page`), tổng số trang (`last_page`), đường dẫn trang tiếp theo (`next_page_url`, trả về `null` nếu đang ở trang cuối), và đường dẫn trang trước (`prev_page_url`, trả về `null` nếu đang ở trang đầu).
5. WHEN người dùng yêu cầu trang vượt quá tổng số trang, THE Search_Engine SHALL trả về Result_Set rỗng với `current_page` bằng trang được yêu cầu, `next_page_url` là `null`, và `prev_page_url` trỏ về trang cuối cùng hợp lệ.
6. WHEN `total = 0` và người dùng yêu cầu bất kỳ trang nào (kể cả page > 1), THE Search_Engine SHALL trả về Result_Set rỗng với `current_page` bằng trang được yêu cầu, `last_page = 1`, `next_page_url = null`, và `prev_page_url = null`.

---

### Requirement 11: Sắp xếp kết quả (Sorting)

**User Story:** Là một ứng viên IT, tôi muốn sắp xếp kết quả tìm kiếm theo các tiêu chí khác nhau, để tôi có thể ưu tiên xem những tin phù hợp nhất.

#### Acceptance Criteria

1. THE Search_Engine SHALL hỗ trợ các tùy chọn sắp xếp sau: `relevance` (theo Relevance_Score, chỉ khi có Keyword), `newest` (theo `created_at` giảm dần), `salary_desc` (theo `salary` giảm dần với các tin `salary = 0` xếp cuối — implement bằng `ORDER BY CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary DESC`), `salary_asc` (theo `salary` tăng dần với các tin `salary = 0` xếp cuối — implement bằng `ORDER BY CASE WHEN salary = 0 THEN 1 ELSE 0 END ASC, salary ASC`), `closing_soon` (theo `application_close_date` tăng dần).
2. WHEN người dùng không cung cấp tham số `sort` và có Keyword, THE Search_Engine SHALL sắp xếp theo `relevance` mặc định.
3. WHEN người dùng không cung cấp tham số `sort` và không có Keyword, THE Search_Engine SHALL sắp xếp theo `newest` mặc định.
4. IF người dùng yêu cầu sắp xếp theo `relevance` nhưng không có Keyword, THEN THE Search_Engine SHALL tự động chuyển sang sắp xếp theo `newest`.
5. IF người dùng cung cấp giá trị `sort` không hợp lệ, THEN THE Search_Engine SHALL áp dụng thứ tự sắp xếp mặc định theo quy tắc tại tiêu chí 2 và 3.

---

### Requirement 12: Chỉ hiển thị tin tuyển dụng đang mở

**User Story:** Là một ứng viên IT, tôi muốn kết quả tìm kiếm chỉ hiển thị các tin tuyển dụng đang còn hiệu lực, để tôi không lãng phí thời gian vào các tin đã đóng.

#### Acceptance Criteria

1. THE Search_Engine SHALL luôn lọc Result_Set chỉ bao gồm các Listing có `status = 'open'`.
2. THE Search_Engine SHALL luôn lọc Result_Set chỉ bao gồm các Listing có `application_close_date >= CURDATE()` (so sánh theo ngày, dùng `CURDATE()` — không dùng `NOW()` — tính theo múi giờ UTC+7 được cấu hình tại database server).
3. IF người dùng cung cấp tham số `status` hoặc `application_close_date` trong Search_Request, THEN THE Search_Engine SHALL bỏ qua các tham số đó và vẫn áp dụng hai điều kiện cố định tại tiêu chí 1 và 2.

---

### Requirement 13: Dữ liệu trả về và hiển thị giá trị đặc biệt

**User Story:** Là một ứng viên IT, tôi muốn mỗi tin tuyển dụng trong kết quả hiển thị đủ thông tin cần thiết với các giá trị đặc biệt được trình bày rõ ràng, để tôi có thể đánh giá nhanh mà không cần vào trang chi tiết.

#### Acceptance Criteria

1. THE Search_Engine SHALL trả về cho mỗi Listing trong Result_Set các trường sau: `id`, `title`, `slug`, `predes_truncated` (nếu `predes` gốc là NULL thì trả về `null`; nếu có giá trị thì cắt tối đa 200 ký tự tại ranh giới từ, thêm "..." nếu bị cắt), `job_type`, `work_mode`, `job_level`, `address`, `salary`, `salary_display`, `experience_years_min`, `experience_years_max`, `experience_display`, `application_close_date`, `created_at`.
2. THE Search_Engine SHALL trả về thông tin Employer của mỗi Listing bao gồm: `company_name`, `company_logo`, `company_size`.
3. THE Search_Engine SHALL trả về danh sách Skill liên kết với mỗi Listing bao gồm: `id`, `name`, `slug`; nếu không có Skill nào thì trả về mảng rỗng `[]`.
4. WHEN Keyword được cung cấp, THE Search_Engine SHALL trả về `relevance_score` (số thực) cho mỗi Listing trong Result_Set; WHEN Keyword không được cung cấp, THE Search_Engine SHALL không bao gồm trường `relevance_score` trong response.
5. THE Search_Engine SHALL tính toán `salary_display` phía server theo quy tắc: IF `salary = 0` THEN `salary_display = "Thỏa thuận"`, ELSE `salary_display` là chuỗi định dạng số có dấu phân cách hàng nghìn kèm đơn vị "VNĐ" (ví dụ: "15,000,000 VNĐ").
6. THE Search_Engine SHALL tính toán `experience_display` phía server theo quy tắc: IF cả `experience_years_min` và `experience_years_max` đều là NULL THEN `experience_display = "Không yêu cầu kinh nghiệm"`, IF chỉ `experience_years_min` có giá trị THEN `experience_display = "Từ {min} năm"`, IF chỉ `experience_years_max` có giá trị THEN `experience_display = "Dưới {max} năm"`, ELSE `experience_display = "Từ {min} đến {max} năm"`.
7. THE Search_Engine SHALL trả về các trường nullable (`predes_truncated`, `job_level`, `company_logo`, `experience_years_min`, `experience_years_max`) với giá trị `null` (không phải chuỗi rỗng `""` hay bị bỏ qua khỏi response).

---

### Requirement 14: Hiệu năng truy vấn

**User Story:** Là một ứng viên IT, tôi muốn kết quả tìm kiếm được trả về nhanh chóng, để tôi có trải nghiệm tìm kiếm mượt mà.

#### Acceptance Criteria

1. THE Search_Engine SHALL sử dụng FULLTEXT INDEX `ft_listings_title_desc` cho tất cả truy vấn tìm kiếm theo Keyword, không dùng LIKE trên cột `title` hoặc `predes`.
2. THE Search_Engine SHALL sử dụng Eager Loading để tải thông tin Employer và danh sách Skill trong cùng một truy vấn, tránh vấn đề N+1 query.
3. THE Search_Engine SHALL chỉ SELECT các cột cần thiết cho Result_Set, không sử dụng `SELECT *` trong bất kỳ truy vấn nào liên quan đến tìm kiếm.
4. THE Search_Engine SHALL đảm bảo tất cả cột được sử dụng trong mệnh đề WHERE hoặc JOIN đều có database index: `idx_listings_status`, `idx_listings_job_type`, `idx_listings_work_mode`, `idx_listings_salary`, `idx_listings_close_date`, `idx_listings_job_level`, `idx_listings_exp_min`, `idx_listings_exp_max`, `ft_listings_title_desc`.

---

### Requirement 15: Validation tham số đầu vào

**User Story:** Là một nhà phát triển, tôi muốn module tìm kiếm xác thực tất cả tham số đầu vào, để hệ thống không bị lỗi hoặc trả về kết quả sai do dữ liệu không hợp lệ.

#### Acceptance Criteria

1. IF Search_Request chứa tham số không thuộc danh sách hợp lệ (`keyword`, `job_type`, `work_mode`, `skills`, `skill_mode`, `address`, `city`, `salary_min`, `salary_max`, `exp_min`, `exp_max`, `job_level`, `company_size`, `sort`, `page`, `per_page`), THEN THE Search_Engine SHALL bỏ qua tham số đó mà không báo lỗi.
2. IF Search_Request chứa tham số `salary_min` hoặc `salary_max` không phải số nguyên không âm trong khoảng 0–999,999,999, THEN THE Search_Engine SHALL trả về lỗi validation HTTP 422 với thông báo nêu rõ tên trường và ràng buộc bị vi phạm bằng tiếng Việt.
3. IF Search_Request chứa tham số `exp_min` hoặc `exp_max` không phải số nguyên không âm trong khoảng 0–99, THEN THE Search_Engine SHALL trả về lỗi validation HTTP 422 với thông báo nêu rõ tên trường và ràng buộc bị vi phạm bằng tiếng Việt.
4. IF Search_Request chứa tham số `job_type` không thuộc danh sách `full-time`, `part-time`, `freelance`, `internship`, THEN THE Search_Engine SHALL bỏ qua tham số đó mà không báo lỗi.
5. IF Search_Request chứa tham số `work_mode` không thuộc danh sách `onsite`, `remote`, `hybrid`, THEN THE Search_Engine SHALL bỏ qua tham số đó mà không báo lỗi.
6. IF Search_Request chứa tham số `job_level` không thuộc danh sách `intern`, `fresher`, `junior`, `middle`, `senior`, `lead`, `manager`, THEN THE Search_Engine SHALL bỏ qua tham số đó mà không báo lỗi.
7. IF Search_Request chứa tham số `company_size` không thuộc danh sách `1-9`, `10-49`, `50-199`, `200-499`, `500+`, THEN THE Search_Engine SHALL bỏ qua tham số đó mà không báo lỗi.
8. IF Search_Request chứa tham số `skill_mode` không thuộc danh sách `and`, `or`, THEN THE Search_Engine SHALL sử dụng giá trị mặc định `and` mà không báo lỗi.
9. IF Search_Request chứa tham số `skills` là mảng chứa các phần tử không phải số nguyên dương, THEN THE Search_Engine SHALL bỏ qua các phần tử không hợp lệ đó mà không báo lỗi.
10. IF Search_Request chứa tham số `skills` có nhiều hơn 15 phần tử hợp lệ, THEN THE Search_Engine SHALL chỉ sử dụng 15 phần tử đầu tiên và bỏ qua phần còn lại mà không báo lỗi.
11. IF Search_Request chứa tham số `page` không phải số nguyên dương, THEN THE Search_Engine SHALL sử dụng giá trị mặc định là 1.
12. THE Search_Engine SHALL giới hạn độ dài tham số `keyword` tối đa 255 ký tự; IF vượt quá, THEN THE Search_Engine SHALL cắt bớt xuống 255 ký tự trước khi xử lý.

---

### Requirement 16: API Endpoints

**User Story:** Là một nhà phát triển frontend, tôi muốn có các endpoint API rõ ràng cho module tìm kiếm, để tôi có thể tích hợp giao diện mà không cần đoán URL hay tham số.

#### Acceptance Criteria

1. THE Search_Engine SHALL cung cấp endpoint tìm kiếm chính: `GET /api/listings/search` — nhận tất cả tham số tìm kiếm và lọc qua query string, trả về Result_Set phân trang theo định dạng JSON.
2. THE Search_Engine SHALL cung cấp endpoint danh sách kỹ năng: `GET /api/skills` — trả về danh sách tất cả Skill hiện có (`id`, `name`, `slug`) dưới dạng mảng JSON, không phân trang.
3. THE Search_Engine SHALL cung cấp endpoint danh sách thành phố: `GET /api/listings/cities` — trả về danh sách các chuỗi địa chỉ thô (raw `address`) distinct từ các Active_Listing dưới dạng mảng JSON, sắp xếp theo thứ tự alphabet. Đây là giá trị nguyên bản từ cột `address`, không qua xử lý hay trích xuất tên thành phố — client chịu trách nhiệm hiển thị và lọc trùng lặp do định dạng nhập liệu tự do.
4. THE Search_Engine SHALL trả về HTTP 200 kèm body JSON cho tất cả request hợp lệ đến ba endpoint trên, kể cả khi Result_Set rỗng.
5. THE Search_Engine SHALL trả về HTTP 422 kèm body JSON chứa trường `errors` (object) và `message` (string) cho tất cả request vi phạm validation theo Requirement 15.
6. THE Search_Engine SHALL trả về HTTP 405 cho tất cả request dùng method khác GET đến ba endpoint trên.
7. THE Search_Engine SHALL bao gồm header `Content-Type: application/json` trong tất cả response từ ba endpoint trên.
