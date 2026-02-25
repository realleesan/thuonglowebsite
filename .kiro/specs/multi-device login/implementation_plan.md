# Multi-Device Login Management System

Hệ thống quản lý đăng nhập đa thiết bị cho website ThuongLo. Khi tài khoản đã đăng nhập trên thiết bị A, nếu thiết bị B đăng nhập cùng tài khoản sẽ yêu cầu xác thực qua 2 cách: (1) OTP qua email, (2) Phê duyệt từ thiết bị A. Tối đa 3 thiết bị / tài khoản.

## User Review Required

IMPORTANT

 **Polling vs WebSocket** : Do hệ thống hiện tại không dùng WebSocket, tôi sẽ dùng **AJAX polling** (mỗi 5 giây) để cập nhật trạng thái real-time giữa thiết bị A và B. Nếu bạn muốn dùng WebSocket thay thế, hãy cho tôi biết.

WARNING

 **PHPMailer config** : Tôi sẽ sử dụng

EmailNotificationService.php có sẵn để gửi OTP. Cần đảm bảo SMTP đã được cấu hình đúng trên hosting.

CAUTION

 **Device fingerprinting** : Tôi ghi nhận thiết bị qua `session_id + user_agent + IP`. Không dùng browser fingerprint nâng cao (canvas, WebGL) vì phức tạp. Điều này có nghĩa nếu user đổi browser trên cùng máy sẽ được coi là thiết bị mới.

---

## Proposed Changes

### Database Schema

#### [NEW]

018_create_device_sessions_table.sql

Bảng lưu các phiên đăng nhập thiết bị:

<pre><div node="[object Object]" class="relative whitespace-pre-wrap word-break-all my-2 rounded-lg bg-list-hover-subtle border border-gray-500/20"><div class="min-h-7 relative box-border flex flex-row items-center justify-between rounded-t border-b border-gray-500/20 px-2 py-0.5"><div class="font-sans text-sm text-ide-text-color opacity-60">sql</div><div class="flex flex-row gap-2 justify-end"><div class="cursor-pointer opacity-70 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-copy h-3.5 w-3.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg></div></div></div><div class="p-3"><div class="w-full h-full text-xs cursor-text"><div class="code-block"><div class="code-line" data-line-number="1" data-line-start="1" data-line-end="1"><div class="line-content"><span class="mtk4">CREATE</span><span class="mtk1"></span><span class="mtk4">TABLE</span><span class="mtk1"></span><span class="mtk7">IF</span><span class="mtk1"></span><span class="mtk4">NOT</span><span class="mtk1"></span><span class="mtk4">EXISTS</span><span class="mtk1"> device_sessions (</span></div></div><div class="code-line" data-line-number="2" data-line-start="2" data-line-end="2"><div class="line-content"><span class="mtk1">    id </span><span class="mtk4">INT</span><span class="mtk1"> AUTO_INCREMENT </span><span class="mtk4">PRIMARY KEY</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="3" data-line-start="3" data-line-end="3"><div class="line-content"><span class="mtk1">    user_id </span><span class="mtk4">INT</span><span class="mtk1"></span><span class="mtk4">NOT NULL</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="4" data-line-start="4" data-line-end="4"><div class="line-content"><span class="mtk1">    session_id </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">128</span><span class="mtk1">) </span><span class="mtk4">NOT NULL</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="5" data-line-start="5" data-line-end="5"><div class="line-content"><span class="mtk1">    device_name </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">255</span><span class="mtk1">),       </span><span class="mtk13">-- parsed from User-Agent</span></div></div><div class="code-line" data-line-number="6" data-line-start="6" data-line-end="6"><div class="line-content"><span class="mtk1">    device_type </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">50</span><span class="mtk1">),        </span><span class="mtk13">-- desktop/mobile/tablet</span></div></div><div class="code-line" data-line-number="7" data-line-start="7" data-line-end="7"><div class="line-content"><span class="mtk1">    browser </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">100</span><span class="mtk1">),           </span></div></div><div class="code-line" data-line-number="8" data-line-start="8" data-line-end="8"><div class="line-content"><span class="mtk1">    os </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">100</span><span class="mtk1">),                </span></div></div><div class="code-line" data-line-number="9" data-line-start="9" data-line-end="9"><div class="line-content"><span class="mtk1">    ip_address </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">45</span><span class="mtk1">),         </span></div></div><div class="code-line" data-line-number="10" data-line-start="10" data-line-end="10"><div class="line-content"><span class="mtk1"></span><span class="mtk4">location</span><span class="mtk1"></span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">255</span><span class="mtk1">),          </span><span class="mtk13">-- geo lookup from IP</span></div></div><div class="code-line" data-line-number="11" data-line-start="11" data-line-end="11"><div class="line-content"><span class="mtk1"></span><span class="mtk4">status</span><span class="mtk1"> ENUM(</span><span class="mtk9">'active'</span><span class="mtk1">,</span><span class="mtk9">'pending'</span><span class="mtk1">,</span><span class="mtk9">'rejected'</span><span class="mtk1">) </span><span class="mtk4">DEFAULT</span><span class="mtk1"></span><span class="mtk9">'active'</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="12" data-line-start="12" data-line-end="12"><div class="line-content"><span class="mtk1">    is_current </span><span class="mtk4">TINYINT</span><span class="mtk1">(</span><span class="mtk5">1</span><span class="mtk1">) </span><span class="mtk4">DEFAULT</span><span class="mtk1"></span><span class="mtk5">0</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="13" data-line-start="13" data-line-end="13"><div class="line-content"><span class="mtk1">    last_activity </span><span class="mtk4">TIMESTAMP</span><span class="mtk1"></span><span class="mtk4">DEFAULT</span><span class="mtk1"> CURRENT_TIMESTAMP,</span></div></div><div class="code-line" data-line-number="14" data-line-start="14" data-line-end="14"><div class="line-content"><span class="mtk1">    created_at </span><span class="mtk4">TIMESTAMP</span><span class="mtk1"></span><span class="mtk4">DEFAULT</span><span class="mtk1"> CURRENT_TIMESTAMP,</span></div></div><div class="code-line" data-line-number="15" data-line-start="15" data-line-end="15"><div class="line-content"><span class="mtk1">    updated_at </span><span class="mtk4">TIMESTAMP</span><span class="mtk1"></span><span class="mtk4">DEFAULT</span><span class="mtk1"> CURRENT_TIMESTAMP </span><span class="mtk4">ON UPDATE</span><span class="mtk1"> CURRENT_TIMESTAMP,</span></div></div><div class="code-line" data-line-number="16" data-line-start="16" data-line-end="16"><div class="line-content"><span class="mtk1"></span><span class="mtk4">FOREIGN KEY</span><span class="mtk1"> (user_id) </span><span class="mtk4">REFERENCES</span><span class="mtk1"> users(id) </span><span class="mtk4">ON DELETE CASCADE</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="17" data-line-start="17" data-line-end="17"><div class="line-content"><span class="mtk1"></span><span class="mtk4">INDEX</span><span class="mtk1"> idx_user_status (user_id, </span><span class="mtk4">status</span><span class="mtk1">),</span></div></div><div class="code-line" data-line-number="18" data-line-start="18" data-line-end="18"><div class="line-content"><span class="mtk1"></span><span class="mtk4">INDEX</span><span class="mtk1"> idx_session (session_id)</span></div></div><div class="code-line" data-line-number="19" data-line-start="19" data-line-end="19"><div class="line-content"><span class="mtk1">);</span></div></div></div></div></div></div></pre>

#### [NEW]

019_create_device_verification_codes_table.sql

Bảng lưu mã OTP xác thực:

<pre><div node="[object Object]" class="relative whitespace-pre-wrap word-break-all my-2 rounded-lg bg-list-hover-subtle border border-gray-500/20"><div class="min-h-7 relative box-border flex flex-row items-center justify-between rounded-t border-b border-gray-500/20 px-2 py-0.5"><div class="font-sans text-sm text-ide-text-color opacity-60">sql</div><div class="flex flex-row gap-2 justify-end"><div class="cursor-pointer opacity-70 hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="lucide lucide-copy h-3.5 w-3.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg></div></div></div><div class="p-3"><div class="w-full h-full text-xs cursor-text"><div class="code-block"><div class="code-line" data-line-number="1" data-line-start="1" data-line-end="1"><div class="line-content"><span class="mtk4">CREATE</span><span class="mtk1"></span><span class="mtk4">TABLE</span><span class="mtk1"></span><span class="mtk7">IF</span><span class="mtk1"></span><span class="mtk4">NOT</span><span class="mtk1"></span><span class="mtk4">EXISTS</span><span class="mtk1"> device_verification_codes (</span></div></div><div class="code-line" data-line-number="2" data-line-start="2" data-line-end="2"><div class="line-content"><span class="mtk1">    id </span><span class="mtk4">INT</span><span class="mtk1"> AUTO_INCREMENT </span><span class="mtk4">PRIMARY KEY</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="3" data-line-start="3" data-line-end="3"><div class="line-content"><span class="mtk1">    user_id </span><span class="mtk4">INT</span><span class="mtk1"></span><span class="mtk4">NOT NULL</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="4" data-line-start="4" data-line-end="4"><div class="line-content"><span class="mtk1">    device_session_id </span><span class="mtk4">INT</span><span class="mtk1"></span><span class="mtk4">NOT NULL</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="5" data-line-start="5" data-line-end="5"><div class="line-content"><span class="mtk1">    code </span><span class="mtk4">VARCHAR</span><span class="mtk1">(</span><span class="mtk5">6</span><span class="mtk1">) </span><span class="mtk4">NOT NULL</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="6" data-line-start="6" data-line-end="6"><div class="line-content"><span class="mtk1">    expires_at </span><span class="mtk4">TIMESTAMP</span><span class="mtk1"></span><span class="mtk4">NOT NULL</span><span class="mtk1">,      </span><span class="mtk13">-- hết hạn sau 5 phút</span></div></div><div class="code-line" data-line-number="7" data-line-start="7" data-line-end="7"><div class="line-content"><span class="mtk1">    last_sent_at </span><span class="mtk4">TIMESTAMP</span><span class="mtk1"></span><span class="mtk4">NULL</span><span class="mtk1">,        </span><span class="mtk13">-- thời điểm gửi gần nhất (cooldown 2 phút)</span></div></div><div class="code-line" data-line-number="8" data-line-start="8" data-line-end="8"><div class="line-content"><span class="mtk1">    attempts </span><span class="mtk4">INT</span><span class="mtk1"></span><span class="mtk4">DEFAULT</span><span class="mtk1"></span><span class="mtk5">0</span><span class="mtk1">,             </span><span class="mtk13">-- số lần nhập sai</span></div></div><div class="code-line" data-line-number="9" data-line-start="9" data-line-end="9"><div class="line-content"><span class="mtk1">    is_used </span><span class="mtk4">TINYINT</span><span class="mtk1">(</span><span class="mtk5">1</span><span class="mtk1">) </span><span class="mtk4">DEFAULT</span><span class="mtk1"></span><span class="mtk5">0</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="10" data-line-start="10" data-line-end="10"><div class="line-content"><span class="mtk1">    created_at </span><span class="mtk4">TIMESTAMP</span><span class="mtk1"></span><span class="mtk4">DEFAULT</span><span class="mtk1"> CURRENT_TIMESTAMP,</span></div></div><div class="code-line" data-line-number="11" data-line-start="11" data-line-end="11"><div class="line-content"><span class="mtk1"></span><span class="mtk4">FOREIGN KEY</span><span class="mtk1"> (user_id) </span><span class="mtk4">REFERENCES</span><span class="mtk1"> users(id) </span><span class="mtk4">ON DELETE CASCADE</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="12" data-line-start="12" data-line-end="12"><div class="line-content"><span class="mtk1"></span><span class="mtk4">FOREIGN KEY</span><span class="mtk1"> (device_session_id) </span><span class="mtk4">REFERENCES</span><span class="mtk1"> device_sessions(id) </span><span class="mtk4">ON DELETE CASCADE</span><span class="mtk1">,</span></div></div><div class="code-line" data-line-number="13" data-line-start="13" data-line-end="13"><div class="line-content"><span class="mtk1"></span><span class="mtk4">INDEX</span><span class="mtk1"> idx_user_code (user_id, code)</span></div></div><div class="code-line" data-line-number="14" data-line-start="14" data-line-end="14"><div class="line-content"><span class="mtk1">);</span></div></div></div></div></div></div></pre>

---

### Backend Model

#### [NEW]

DeviceAccessModel.php

Thao tác CRUD với database, bao gồm:

* `getActiveDevices($userId)` - Lấy danh sách thiết bị active
* `getPendingDevices($userId)` - Lấy danh sách thiết bị đang chờ duyệt
* `getDeviceCount($userId)` - Đếm số thiết bị active
* `createDeviceSession($data)` - Tạo phiên thiết bị mới
* `updateDeviceStatus($id, $status)` - Cập nhật trạng thái
* `deleteDeviceSession($id)` - Xóa phiên thiết bị
* `findBySessionId($sessionId)` - Tìm theo session ID
* `findByUserAndSession($userId, $sessionId)` - Tìm phiên hiện tại
* `createVerificationCode($userId, $deviceSessionId)` - Tạo OTP 6 số
* `validateVerificationCode($userId, $code)` - Kiểm tra OTP
* `canResendCode($deviceSessionId)` - Kiểm tra cooldown 2 phút
* `deactivateOtherSessions($userId, $keepSessionId)` - Hủy các phiên khác
* `parseUserAgent($ua)` - Parse browser/OS/device từ User-Agent
* `getLocationFromIP($ip)` - Ước lượng vị trí từ IP (dùng free API)

---

### Backend Service

#### [NEW]

DeviceAccessService.php

Logic nghiệp vụ chính:

* `checkDeviceOnLogin($userId)` → kiểm tra xem tài khoản đã có thiết bị active chưa, nếu đã đủ 3 → trả `requires_verification`
* `initiateEmailVerification($userId, $email, $deviceSessionId)` → kiểm tra email khớp, tạo OTP, gửi email qua PHPMailer
* `verifyOTP($userId, $code)` → xác thực OTP, nếu đúng → activate device, deactivate thiết bị cũ nhất
* `resendOTP($userId, $deviceSessionId)` → kiểm tra cooldown, tạo OTP mới
* `approveDevice($userId, $deviceSessionId, $password)` → kiểm tra password 2 lần, activate device, deactivate thiết bị hiện tại
* `rejectDevice($deviceSessionId)` → đổi status thành `rejected`
* `removeDevice($userId, $deviceId)` → xóa device, nếu là thiết bị hiện tại → đăng xuất
* `getDeviceList($userId)` → danh sách tất cả devices
* `pollDeviceStatus($deviceSessionId)` → thiết bị B poll để biết đã được duyệt/từ chối chưa

---

### Controller & API Updates

#### [MODIFY]

AuthController.php

Thêm methods mới:

* `processLoginWithDeviceCheck()` - Thay thế logic trong

  processLogin(): sau khi xác thực thành công, kiểm tra device limit. Nếu vượt quá → tạo pending device session → trả về JSON yêu cầu xác thực
* `verifyEmail()` - API endpoint nhận email, kiểm tra khớp, gửi OTP
* `verifyOTP()` - API endpoint nhận OTP code
* `resendOTP()` - API endpoint gửi lại OTP
* `pollVerificationStatus()` - API endpoint thiết bị B poll trạng thái

#### [MODIFY]

api.php

Thêm routes mới:

| Route                   | Method | Mô tả                              |
| ----------------------- | ------ | ------------------------------------ |
| `device/verify-email` | POST   | Gửi email để nhận OTP            |
| `device/verify-otp`   | POST   | Xác thực OTP                       |
| `device/resend-otp`   | POST   | Gửi lại OTP (cooldown 2p)          |
| `device/poll-status`  | GET    | Poll trạng thái xác thực         |
| `device/list`         | GET    | Danh sách thiết bị                |
| `device/approve`      | POST   | Duyệt thiết bị (từ thiết bị A) |
| `device/reject`       | POST   | Từ chối thiết bị                 |
| `device/remove`       | POST   | Xóa thiết bị                      |
| `device/pending`      | GET    | Danh sách thiết bị chờ duyệt    |

---

### Login Flow Modification

#### [MODIFY]

AuthService.php

Cập nhật method

authenticate():

1. Sau khi xác thực user/password thành công
2. Gọi `DeviceAccessService::checkDeviceOnLogin()`
3. Nếu cần xác thực → **không** tạo session, trả `requires_device_verification: true`
4. Nếu không cần → tạo session bình thường + ghi device session

#### [MODIFY]

AuthController.php

Cập nhật

processLogin():

* Nhận kết quả từ AuthService
* Nếu `requires_device_verification` → set flash + redirect về login kèm popup xác thực
* Lưu tạm `pending_user_id` và `pending_device_session_id` vào session

---

### Frontend - Login Verification Popup

Popup sẽ hiển thị khi đăng nhập bị chặn bởi device limit. Có 2 tab:

**Tab 1: Xác thực qua Email**

1. Input nhập email → nút "Gửi mã"
2. Nếu email sai → thông báo lỗi
3. Nếu email đúng → gửi OTP qua PHPMailer → hiện input nhập mã 6 số
4. Timer đếm ngược 5 phút (hết hạn mã)
5. Nút "Gửi lại mã" với cooldown 120 giây
6. Nhập đúng mã → đăng nhập thành công, thiết bị cũ nhất đăng xuất

**Tab 2: Yêu cầu phê duyệt từ thiết bị hiện có**

* Hiển thị thông báo "Đã gửi yêu cầu đến thiết bị đang đăng nhập"
* Poll mỗi 5 giây để kiểm tra trạng thái
* Nếu được duyệt → tự động đăng nhập
* Nếu bị từ chối → thông báo "Đăng nhập thất bại"

---

### Frontend - Access Management Page

#### [MODIFY]

user_sidebar.php

Thêm tab "Truy cập" với icon `fas fa-shield-alt` vào sidebar, sau mục "Yêu thích" và trước divider.

#### [MODIFY]

index.php

Thêm routing cho module

access trong switch case `users`, tương tự cấu trúc `account` module.

#### [MODIFY]

index.php

Trang chính hiển thị:

1. **Thông báo thiết bị lạ** (nếu có pending requests) - card nổi bật với thông tin thiết bị B (tên, IP, OS, browser, vị trí, thời gian). Hai nút: "Đó là tôi" và "Thiết bị lạ"
2. **Danh sách thiết bị đang truy cập** - bảng/cards hiển thị tất cả active devices với thông tin chi tiết. Mỗi thiết bị có nút "Xóa" (icon `fa-trash`). Thiết bị hiện tại được đánh dấu "(Thiết bị này)"
3. **Thông tin giới hạn** - hiển thị `x/3 thiết bị đang sử dụng`

#### [MODIFY]

user_access.css

Styling cho:

* Device cards với gradient header
* Pending alert banner với animation nhấp nháy
* Device info grid
* Popup xác nhận mật khẩu (modal)
* Popup xác thực OTP trên trang login
* Timer countdown styling
* Responsive design cho mobile

#### [MODIFY]

user_access.js

JavaScript xử lý:

* AJAX load danh sách thiết bị
* Polling kiểm tra pending requests (mỗi 5 giây)
* Approve flow: hiện popup nhập password → gọi API approve
* Reject flow: gọi API reject → UI update
* Remove flow: confirm → gọi API remove → nếu current device → redirect logout
* OTP flow: email validate → send OTP → countdown timers → verify OTP
* Resend cooldown timer (120 giây)
* OTP expiry timer (5 phút)

---

### Email Service

#### [MODIFY]

EmailNotificationService.php

Thêm method:

* `sendDeviceVerificationCode($email, $userName, $code, $deviceInfo)` - Gửi email chứa OTP 6 số với template HTML chuyên nghiệp, bao gồm thông tin thiết bị đang yêu cầu truy cập

---

## Verification Plan

### Automated Tests

Không có test framework tự động hiện tại (phpunit.xml tồn tại nhưng tests chủ yếu là functional scripts).

### Manual Verification (Browser Testing)

IMPORTANT

Tất cả test sẽ được thực hiện trên browser. Tôi sẽ sử dụng browser tool để navigate và kiểm tra.

**Test 1: Đăng nhập thiết bị đầu tiên (baseline)**

1. Mở browser → `https://test1.web3b.com/?page=login`
2. Đăng nhập tài khoản → Phải thành công bình thường
3. Vào `?page=users&module=access` → Phải thấy 1 thiết bị active

**Test 2: Migration + Database**

1. Chạy migration script
2. Kiểm tra bảng `device_sessions` và `device_verification_codes` đã tạo

**Test 3: Access Page UI**

1. Đăng nhập → Vào `?page=users&module=access`
2. Kiểm tra sidebar có tab "Truy cập"
3. Kiểm tra thấy thiết bị hiện tại trong danh sách
4. Kiểm tra hiển thị "1/3 thiết bị"

**Test 4: Xóa thiết bị hiện tại**

1. Ở trang Access, bấm xóa thiết bị hiện tại
2. Phải bị đăng xuất ngay lập tức

NOTE

Test đầy đủ luồng 2 thiết bị (email OTP và approve) cần bạn hỗ trợ kiểm tra thủ công vì cần 2 trình duyệt/thiết bị khác nhau. Tôi sẽ đảm bảo code hoạt động đúng logic và UI hiển thị chính xác.
