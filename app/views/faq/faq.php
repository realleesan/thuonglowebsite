<?php
/**
 * FAQ Page
 * Standardized with View Initialization System
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho faq (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 2. Khởi tạo biến dữ liệu
$faqData = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Lấy dữ liệu từ Service
    if ($service && method_exists($service, 'getFaqPageData')) {
        $faqData = $service->getFaqPageData();
    } else {
        $faqData = [];
    }
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'faq', []);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
}
?>
<!-- FAQ Page Content -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-faq">
                <?php 
                // Breadcrumb sẽ được hiển thị từ master layout
                ?>


                <!-- FAQ Section -->
                <section class="faq-section">
                    <div class="container">
                        <div class="section-header">
                            <h2 class="section-title">Câu hỏi thường gặp</h2>
                            <p class="section-subtitle">Các câu hỏi được chúng tôi tổng hợp từ khách hàng</p>
                        </div>
                        
                        <div class="faq-content">
                            <div class="faq-categories">
                                <div class="faq-category">
                                    <h3>Đơn hàng & Thanh toán</h3>
                                    <div class="faq-items">
                                        <div class="faq-item">
                                            <h4 class="faq-question">Làm thế nào để đặt hàng trên ThuongLo?</h4>
                                            <div class="faq-answer">
                                                <p>Bạn có thể đặt hàng trực tiếp trên website bằng cách chọn sản phẩm, thêm vào giỏ hàng và tiến hành thanh toán. Chúng tôi hỗ trợ nhiều hình thức thanh toán tiện lợi.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Các phương thức thanh toán nào được chấp nhận?</h4>
                                            <div class="faq-answer">
                                                <p>Chúng tôi chấp nhận thanh toán khi nhận hàng (COD), chuyển khoản ngân hàng, thẻ tín dụng/ghi nợ, ví điện tử và trả góp 0%.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Làm thế nào để theo dõi đơn hàng của tôi?</h4>
                                            <div class="faq-answer">
                                                <p>Sau khi đặt hàng, bạn sẽ nhận được mã vận đơn qua email/SMS. Bạn có thể theo dõi đơn hàng trên website hoặc liên hệ hotline để được hỗ trợ.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-category">
                                    <h3>Sản phẩm & Chất lượng</h3>
                                    <div class="faq-items">
                                        <div class="faq-item">
                                            <h4 class="faq-question">Sản phẩm có chính hãng không?</h4>
                                            <div class="faq-answer">
                                                <p>Tất cả sản phẩm trên ThuongLo đều được cam kết chính hãng 100%, có nguồn gốc rõ ràng và đầy đủ giấy tờ chứng nhận chất lượng.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Làm thế nào để chọn size phù hợp?</h4>
                                            <div class="faq-answer">
                                                <p>Mỗi sản phẩm đều có bảng size chi tiết. Bạn có thể tham khảo bảng size hoặc liên hệ tư vấn để được đo size miễn phí và chọn size phù hợp nhất.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Sản phẩm có được bảo hành không?</h4>
                                            <div class="faq-answer">
                                                <p>Có, tất cả sản phẩm đều được bảo hành theo chính sách của từng thương hiệu. Thời gian bảo hành từ 6-24 tháng tùy loại sản phẩm.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-category">
                                    <h3>Giao hàng & Đổi trả</h3>
                                    <div class="faq-items">
                                        <div class="faq-item">
                                            <h4 class="faq-question">Thời gian giao hàng bao lâu?</h4>
                                            <div class="faq-answer">
                                                <p>Thời gian giao hàng nội thành 2-3 ngày, các tỉnh khác 3-5 ngày. Chúng tôi có dịch vụ giao hàng nhanh trong 24 giờ cho các đơn hàng gấp.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Chính sách đổi trả như thế nào?</h4>
                                            <div class="faq-answer">
                                                <p>Bạn có thể đổi trả sản phẩm trong vòng 30 ngày nếu còn nguyên tag, chưa qua sử dụng và có hóa đơn. Chúng tôi hỗ trợ đổi trả miễn phí tại nhà.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Có giao hàng ra nước ngoài không?</h4>
                                            <div class="faq-answer">
                                                <p>Hiện tại chúng tôi chỉ giao hàng trong lãnh thổ Việt Nam. Trong tương lai chúng tôi sẽ mở rộng dịch vụ giao hàng quốc tế.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-category">
                                    <h3>Tài khoản & Ưu đãi</h3>
                                    <div class="faq-items">
                                        <div class="faq-item">
                                            <h4 class="faq-question">Làm thế nào để tạo tài khoản?</h4>
                                            <div class="faq-answer">
                                                <p>Bạn có thể tạo tài khoản miễn phí trong 30 giây bằng email hoặc số điện thoại. Tài khoản giúp bạn theo dõi đơn hàng và tích điểm thưởng.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Chương trình thành viên có lợi ích gì?</h4>
                                            <div class="faq-answer">
                                                <p>Thành viên được tích điểm đổi quà, giảm giá sinh nhật, ưu tiên xem sản phẩm mới và nhiều đặc quyền khác tùy hạng thành viên.</p>
                                            </div>
                                        </div>
                                        <div class="faq-item">
                                            <h4 class="faq-question">Làm thế nào để nhận mã giảm giá?</h4>
                                            <div class="faq-answer">
                                                <p>Theo dõi fanpage, đăng ký email newsletter, kiểm tra app thường xuyên để không bỏ lỡ các mã giảm giá và chương trình khuyến mãi hấp dẫn.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
