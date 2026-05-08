<?php
/**
 * Terms of Service Page
 * Standardized with View Initialization System
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho terms (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 2. Khởi tạo biến dữ liệu
$termsData = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Lấy dữ liệu từ Service
    if ($service && method_exists($service, 'getTermsPageData')) {
        $termsData = $service->getTermsPageData();
    } else {
        $termsData = [];
    }
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'terms', []);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
}
?>
<!-- Terms of Service Page Content -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-terms">
                <?php 
                // Breadcrumb sẽ được hiển thị từ master layout
                ?>

                

                <!-- Terms Content -->
                <section class="terms-section">
                    <div class="container">
                        <div class="terms-content">
                            

                            <div class="terms-sections">
                                <div class="terms-section-item">
                                    <h2>1. Chấp nhận Điều khoản</h2>
                                    <p>Bằng việc truy cập và sử dụng website ThuongLo, bạn xác nhận đã đọc, hiểu và đồng ý bị ràng buộc bởi các điều khoản này. Nếu bạn không đồng ý với bất kỳ phần nào của điều khoản, vui lòng không sử dụng dịch vụ của chúng tôi.</p>
                                </div>

                                <div class="terms-section-item">
                                    <h2>2. Định nghĩa</h2>
                                    <ul>
                                        <li><strong>Website:</strong> Nền tảng thương mại điện tử ThuongLo tại địa chỉ thuonglo.com</li>
                                        <li><strong>Dịch vụ:</strong> Các sản phẩm và dịch vụ được cung cấp trên website</li>
                                        <li><strong>Người dùng:</strong> Bất kỳ cá nhân hoặc tổ chức truy cập hoặc sử dụng website</li>
                                        <li><strong>Khách hàng:</strong> Người dùng thực hiện mua hàng trên website</li>
                                        <li><strong>Sản phẩm:</strong> Các mặt hàng được bán trên nền tảng ThuongLo</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>3. Đăng ký tài khoản</h2>
                                    <p>Để sử dụng đầy đủ tính năng, bạn cần đăng ký tài khoản với các yêu cầu:</p>
                                    <ul>
                                        <li>Cung cấp thông tin cá nhân chính xác, đầy đủ và cập nhật</li>
                                        <li>Bảo mật tài khoản và mật khẩu đăng nhập</li>
                                        <li>Chịu trách nhiệm cho mọi hoạt động dưới tài khoản của bạn</li>
                                        <li>Thông báo ngay cho chúng tôi khi phát hiện tài khoản bị xâm phạm</li>
                                        <li>Không tạo nhiều tài khoản cho cùng một mục đích</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>4. Sản phẩm và Giá cả</h2>
                                    <p>Chúng tôi cam kết:</p>
                                    <ul>
                                        <li>Cung cấp sản phẩm chính hãng 100%</li>
                                        <li>Thông tin sản phẩm chính xác và đầy đủ</li>
                                        <li>Giá cả cạnh tranh và minh bạch</li>
                                        <li>Giá có thể thay đổi mà không cần thông báo trước</li>
                                        <li>Không chịu trách nhiệm cho lỗi kỹ thuật hiển thị giá</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>5. Đặt hàng và Thanh toán</h2>
                                    <p>Quy trình đặt hàng và thanh toán:</p>
                                    <ul>
                                        <li>Đơn hàng chỉ được xác nhận sau khi thanh toán thành công</li>
                                        <li>Chúng tôi có quyền hủy đơn hàng nếu phát hiện gian lận</li>
                                        <li>Khách hàng chịu trách nhiệm cung cấp thông tin giao hàng chính xác</li>
                                        <li>Phí vận chuyển sẽ được hiển thị rõ ràng trong quá trình thanh toán</li>
                                        <li>Chúng tôi hỗ trợ nhiều phương thức thanh toán an toàn</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>6. Giao hàng và Nhận hàng</h2>
                                    <p>Về vận chuyển và nhận hàng:</p>
                                    <ul>
                                        <li>Thời gian giao hàng dự kiến: 2-5 ngày làm việc</li>
                                        <li>Khách hàng cần kiểm tra hàng hóa trước khi nhận</li>
                                        <li>Chúng tôi không chịu trách nhiệm cho sai sót thông tin địa chỉ</li>
                                        <li>Trường hợp hàng hóa hư hỏng, chúng tôi sẽ đổi trả miễn phí</li>
                                        <li>Rủi ro mất mát chuyển sang cho khách hàng khi nhận hàng thành công</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>7. Đổi trả và Hoàn tiền</h2>
                                    <p>Chính sách đổi trả:</p>
                                    <ul>
                                        <li>Đổi trả trong vòng 30 ngày kể từ ngày nhận hàng</li>
                                        <li>Sản phẩm còn nguyên tag, chưa qua sử dụng</li>
                                        <li>Cung cấp hóa đơn mua hàng và tem bảo hành</li>
                                        <li>Chi phí vận chuyển đổi trả do khách hàng chịu</li>
                                        <li>Hoàn tiền trong 5-7 ngày làm việc sau khi nhận hàng trả lại</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>8. Quyền sở hữu trí tuệ</h2>
                                    <p>Toàn bộ nội dung trên website ThuongLo bao gồm:</p>
                                    <ul>
                                        <li>Thiết kế, logo, hình ảnh, văn bản là tài sản của ThuongLo</li>
                                        <li>Bảo vệ bởi luật bản quyền và sở hữu trí tuệ</li>
                                        <li>Nghiêm cấm sao chép, phân phối mà không có sự cho phép</li>
                                        <li>Vi phạm sẽ được xử lý theo quy định pháp luật</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>9. Hành vi bị cấm</h2>
                                    <p>Người dùng không được:</p>
                                    <ul>
                                        <li>Sử dụng website cho mục đích bất hợp pháp</li>
                                        <li>Cố gắng xâm nhập hệ thống hoặc gây hại</li>
                                        <li>Đăng tải nội dung vi phạm đạo đức, pháp luật</li>
                                        <li>Thực hiện hành vi gian lận hoặc lừa đảo</li>
                                        <li>Can thiệp vào hoạt động bình thường của website</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>10. Bảo mật thông tin</h2>
                                    <p>Chúng tôi cam kết:</p>
                                    <ul>
                                        <li>Bảo vệ thông tin cá nhân của khách hàng</li>
                                        <li>Không chia sẻ thông tin cho bên thứ ba</li>
                                        <li>Áp dụng các biện pháp an ninh hiện đại</li>
                                        <li>Tuân thủ Luật Bảo mật dữ liệu cá nhân</li>
                                        <li>Thông tin chỉ được sử dụng cho mục đích phục vụ khách hàng</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>11. Hạn chế trách nhiệm</h2>
                                    <p>ThuongLo không chịu trách nhiệm cho:</p>
                                    <ul>
                                        <li>Tổn thất gián tiếp phát sinh từ việc sử dụng website</li>
                                        <li>Lỗi kỹ thuật từ bên thứ ba (ngân hàng, vận chuyển)</li>
                                        <li>Sự cố bất khả kháng (thiên tai, chiến tranh)</li>
                                        <li>Nội dung từ các liên kết ngoài website</li>
                                        <li>Hành vi của người dùng khác</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>12. Giải quyết tranh chấp</h2>
                                    <p>Mọi tranh chấp sẽ được giải quyết theo quy trình:</p>
                                    <ul>
                                        <li>Ưu tiên thương lượng, hòa giải giữa các bên</li>
                                        <li>Áp dụng pháp luật Việt Nam</li>
                                        <li>Tòa án nhân dân có thẩm quyền sẽ giải quyết</li>
                                        <li>Chi phí phát sinh sẽ do bên vi phạm chịu</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>13. Thay đổi điều khoản</h2>
                                    <p>Chúng tôi có quyền:</p>
                                    <ul>
                                        <li>Thay đổi, cập nhật điều khoản định kỳ</li>
                                        <li>Thông báo thay đổi qua email hoặc website</li>
                                        <li>Điều khoản mới có hiệu lực ngay khi đăng tải</li>
                                        <li>Việc tiếp tục sử dụng đồng nghĩa với chấp nhận điều khoản mới</li>
                                    </ul>
                                </div>

                                <div class="terms-section-item">
                                    <h2>14. Liên hệ</h2>
                                    <p>Nếu có câu hỏi về điều khoản dịch vụ, vui lòng liên hệ:</p>
                                    <ul>
                                        <li>Email: support@thuonglo.com</li>
                                        <li>Hotline: 1900-1234</li>
                                        <li>Địa chỉ: 123 Nguyễn Huệ, Quận 1, TP.HCM</li>
                                        <li>Thời gian: 8:00 - 22:00 hàng ngày</li>
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
