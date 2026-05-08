<?php
/**
 * Privacy Policy Page
 * Standardized with View Initialization System
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho privacy (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 2. Khởi tạo biến dữ liệu
$privacyData = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Lấy dữ liệu từ Service
    if ($service && method_exists($service, 'getPrivacyPageData')) {
        $privacyData = $service->getPrivacyPageData();
    } else {
        $privacyData = [];
    }
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'privacy', []);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
}
?>
<!-- Privacy Policy Page Content -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-privacy">
                <?php 
                // Breadcrumb sẽ được hiển thị từ master layout
                ?>

              

                <!-- Privacy Content -->
                <section class="privacy-section">
                    <div class="container">
                        <div class="privacy-content">
                            

                            <div class="privacy-sections">
                                <div class="privacy-section-item">
                                    <h2>1. Thông tin chúng tôi thu thập</h2>
                                    <p>Chúng tôi có thể thu thập các loại thông tin sau:</p>
                                    <ul>
                                        <li><strong>Thông tin cá nhân:</strong> Họ tên, email, số điện thoại, địa chỉ, ngày sinh</li>
                                        <li><strong>Thông tin tài khoản:</strong> Tên đăng nhập, mật khẩu (đã mã hóa), lịch sử đăng nhập</li>
                                        <li><strong>Thông tin giao dịch:</strong> Lịch sử mua hàng, phương thức thanh toán, địa chỉ giao hàng</li>
                                        <li><strong>Thông tin thiết bị:</strong> IP address, loại trình duyệt, hệ điều hành, thiết bị sử dụng</li>
                                        <li><strong>Thông tin sử dụng:</strong> Lịch sử duyệt web, sản phẩm xem, thời gian truy cập, tìm kiếm</li>
                                        <li><strong>Thông tin marketing:</strong> Lựa chọn nhận email, SMS, thông báo đẩy</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>2. Cách chúng tôi thu thập thông tin</h2>
                                    <p>Thông tin được thu thập thông qua:</p>
                                    <ul>
                                        <li><strong>Form đăng ký:</strong> Khi bạn tạo tài khoản hoặc đăng ký nhận tin</li>
                                        <li><strong>Quá trình mua hàng:</strong> Khi đặt hàng và thanh toán</li>
                                        <li><strong>Cookies:</strong> Để ghi nhớ preferences và cải thiện trải nghiệm</li>
                                        <li><strong>Analytics tools:</strong> Google Analytics để phân tích traffic website</li>
                                        <li><strong>Tương tác trực tiếp:</strong> Chat, email, điện thoại với đội ngũ hỗ trợ</li>
                                        <li><strong>Social media:</strong> Khi bạn kết nối qua mạng xã hội</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>3. Mục đích sử dụng thông tin</h2>
                                    <p>Chúng tôi sử dụng thông tin của bạn để:</p>
                                    <ul>
                                        <li>Cung cấp dịch vụ và xử lý đơn hàng</li>
                                        <li>Cá nhân hóa trải nghiệm mua sắm</li>
                                        <li>Gửi thông báo về đơn hàng và vận chuyển</li>
                                        <li>Cung cấp hỗ trợ khách hàng</li>
                                        <li>Gửi thông tin khuyến mãi (với sự cho phép của bạn)</li>
                                        <li>Cải thiện sản phẩm và dịch vụ</li>
                                        <li>Phòng chống gian lận và bảo mật</li>
                                        <li>Thực hiện các nghĩa vụ pháp lý</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>4. Chia sẻ thông tin với bên thứ ba</h2>
                                    <p>Chúng tôi chỉ chia sẻ thông tin trong các trường hợp:</p>
                                    <ul>
                                        <li><strong>Đối tác dịch vụ:</strong> Đơn vị vận chuyển, thanh toán, logistics</li>
                                        <li><strong>Nhà cung cấp:</strong> Để xử lý đơn hàng và bảo hành sản phẩm</li>
                                        <li><strong>Cơ quan pháp luật:</strong> Khi có yêu cầu hợp lệ từ cơ quan chức năng</li>
                                        <li><strong>Mua bán sáp nhập:</strong> Khi có sự thay đổi cấu trúc công ty</li>
                                        <li><strong>Quảng cáo:</strong> Các nền tảng quảng cáo (với sự đồng ý của bạn)</li>
                                    </ul>
                                    <p>Tất cả các bên thứ ba đều phải tuân thủ tiêu chuẩn bảo mật dữ liệu của chúng tôi.</p>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>5. Bảo mật thông tin</h2>
                                    <p>Chúng tôi áp dụng các biện pháp bảo mật:</p>
                                    <ul>
                                        <li><strong>Mã hóa dữ liệu:</strong> SSL/TLS cho tất cả kết nối</li>
                                        <li><strong>Bảo mật mật khẩu:</strong> Mã hóa theo chuẩn bcrypt</li>
                                        <li><strong>Firewall:</strong> Chặn các truy cập độc hại</li>
                                        <li><strong>Antivirus:</strong> Quét và phòng chống malware</li>
                                        <li><strong>Access control:</strong> Giới hạn quyền truy cập dữ liệu</li>
                                        <li><strong>Backup:</strong> Sao lưu dữ liệu định kỳ</li>
                                        <li><strong>Audit log:</strong> Ghi lại mọi truy cập dữ liệu</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>6. Quyền của bạn</h2>
                                    <p>Bạn có các quyền sau đối với thông tin cá nhân:</p>
                                    <ul>
                                        <li><strong>Quyền truy cập:</strong> Yêu cầu xem thông tin chúng tôi lưu trữ về bạn</li>
                                        <li><strong>Quyền sửa đổi:</strong> Cập nhật hoặc chỉnh sửa thông tin cá nhân</li>
                                        <li><strong>Quyền xóa:</strong> Yêu cầu xóa tài khoản và thông tin liên quan</li>
                                        <li><strong>Quyền hạn chế:</strong> Giới hạn việc xử lý thông tin của bạn</li>
                                        <li><strong>Quyền phản đối:</strong> Chống lại việc sử dụng thông tin cho marketing</li>
                                        <li><strong>Quyền di chuyển:</strong> Yêu cầu chuyển dữ liệu sang nhà cung cấp khác</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>7. Cookies và Tracking</h2>
                                    <p>Website sử dụng cookies để:</p>
                                    <ul>
                                        <li>Ghi nhớ thông tin đăng nhập và giỏ hàng</li>
                                        <li>Cá nhân hóa nội dung và quảng cáo</li>
                                        <li>Phân tích hành vi người dùng</li>
                                        <li>Cải thiện hiệu suất website</li>
                                    </ul>
                                    <p>Bạn có thể quản lý cookies qua cài đặt trình duyệt. Tuy nhiên, việc vô hiệu hóa cookies có thể ảnh hưởng đến trải nghiệm sử dụng.</p>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>8. Lưu trữ dữ liệu</h2>
                                    <p>Chính sách lưu trữ của chúng tôi:</p>
                                    <ul>
                                        <li><strong>Tài khoản:</strong> Lưu trữ vĩnh viễn hoặc cho đến khi bạn yêu cầu xóa</li>
                                        <li><strong>Đơn hàng:</strong> Lưu trữ 5 năm cho mục đích bảo hành và thuế</li>
                                        <li><strong>Log hệ thống:</strong> Lưu trữ 90 ngày</li>
                                        <li><strong>Marketing data:</strong> Lưu trữ 2 năm sau khi unsubscribed</li>
                                        <li><strong>Payment data:</strong> Lưu trữ theo quy định của Ngân hàng Nhà nước</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>9. Bảo vệ trẻ em</h2>
                                    <p>Chúng tôi không thu thập thông tin cá nhân của trẻ em dưới 16 tuổi. Nếu phát hiện có thông tin trẻ em, chúng tôi sẽ:</p>
                                    <ul>
                                        <li>Xóa ngay lập tức thông tin liên quan</li>
                                        <li>Thông báo cho phụ huynh/người giám hộ</li>
                                        <li>Cung cấp hướng dẫn bảo vệ trẻ em online</li>
                                    </ul>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>10. Thay đổi chính sách bảo mật</h2>
                                    <p>Chúng tôi có thể cập nhật chính sách này khi:</p>
                                    <ul>
                                        <li>Thay đổi về pháp luật hoặc quy định</li>
                                        <li>Cập nhật công nghệ hoặc quy trình</li>
                                        <li>Mở rộng hoặc thay đổi dịch vụ</li>
                                        <li>Phản hồi từ khách hàng</li>
                                    </ul>
                                    <p>Mọi thay đổi sẽ được thông báo qua email và đăng tải trên website trước 30 ngày áp dụng.</p>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>11. Liên hệ chúng tôi</h2>
                                    <p>Nếu có câu hỏi hoặc yêu cầu về quyền riêng tư, vui lòng liên hệ:</p>
                                    <ul>
                                        <li><strong>Email:</strong> privacy@thuonglo.com</li>
                                        <li><strong>Hotline:</strong> 1900-1234 (nhấn 3)</li>
                                        <li><strong>Địa chỉ:</strong> 123 Nguyễn Huệ, Quận 1, TP.HCM</li>
                                        <li><strong>Bộ phận:</strong> Chịu trách nhiệm bảo vệ dữ liệu cá nhân</li>
                                    </ul>
                                    <p>Chúng tôi cam kết phản hồi trong vòng 24 giờ làm việc.</p>
                                </div>

                                <div class="privacy-section-item">
                                    <h2>12. Khiếu nại và giải quyết</h2>
                                    <p>Nếu bạn cho rằng quyền riêng tư bị vi phạm:</p>
                                    <ul>
                                        <li>Liên hệ ngay với bộ phận bảo mật của chúng tôi</li>
                                        <li>Cung cấp bằng chứng và mô tả chi tiết</li>
                                        <li>Chúng tôi sẽ điều tra trong vòng 7 ngày làm việc</li>
                                        <li>Nếu không hài lòng, bạn có thể khiếu nại lên Cục An toàn thông tin</li>
                                    </ul>
                                </div>
                            </div>

                          
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
