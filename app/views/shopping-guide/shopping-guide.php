<?php
/**
 * Shopping Guide Page
 * Standardized with View Initialization System
 */

// 1. Khởi tạo View an toàn & ServiceManager
require_once __DIR__ . '/../../../core/view_init.php';

// Chọn service phù hợp cho shopping guide (ưu tiên inject từ routing)
$service = isset($currentService) ? $currentService : ($publicService ?? null);

// 2. Khởi tạo biến dữ liệu
$guideData = [];
$showErrorMessage = false;
$errorMessage = '';

try {
    // Lấy dữ liệu từ Service
    if ($service && method_exists($service, 'getShoppingGuidePageData')) {
        $guideData = $service->getShoppingGuidePageData();
    } else {
        $guideData = [];
    }
    
} catch (Exception $e) {
    if (isset($errorHandler)) {
        $result = $errorHandler->handleViewError($e, 'shopping-guide', []);
        $showErrorMessage = true;
        $errorMessage = $result['message'];
    }
}
?>
<!-- Shopping Guide Page Content -->
<?php if ($showErrorMessage): ?>
<div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;">
    <strong>Thông báo:</strong> <?php echo htmlspecialchars($errorMessage); ?>
</div>
<?php endif; ?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-shopping-guide">
                <?php 
                // Breadcrumb sẽ được hiển thị từ master layout
                ?>

                

                <!-- Guide Steps -->
                <section class="guide-section">
                    <div class="container">
                        <div class="section-header">
                            <h2 class="section-title">4 bước mua hàng đơn giản</h2>
                            <p class="section-subtitle">Chỉ trong vài phút để sở hữu sản phẩm yêu thích</p>
                        </div>
                        
                        <div class="steps-container">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h3>Tìm kiếm sản phẩm</h3>
                                    <p>Sử dụng thanh tìm kiếm hoặc duyệt theo danh mục, thương hiệu để tìm sản phẩm bạn muốn.</p>
                                    <ul class="step-tips">
                                        <li>Nhập tên sản phẩm vào ô tìm kiếm</li>
                                        <li>Sử dụng bộ lọc để tìm chính xác hơn</li>
                                        <li>Xem đánh giá từ khách hàng khác</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h3>Chọn size và thêm vào giỏ</h3>
                                    <p>Chọn size phù hợp, màu sắc và số lượng, sau đó thêm vào giỏ hàng.</p>
                                    <ul class="step-tips">
                                        <li>Tham khảo bảng size chi tiết</li>
                                        <li>Kiểm tra tồn kho sản phẩm</li>
                                        <li>Thêm nhiều sản phẩm vào giỏ hàng</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h3>Kiểm tra và thanh toán</h3>
                                    <p>Kiểm tra lại thông tin đơn hàng, nhập địa chỉ và chọn phương thức thanh toán.</p>
                                    <ul class="step-tips">
                                        <li>Xem lại sản phẩm và số lượng</li>
                                        <li>Nhập thông tin giao hàng chính xác</li>
                                        <li>Chọn phương thức thanh toán phù hợp</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h3>Nhận hàng và kiểm tra</h3>
                                    <p>Nhận hàng, kiểm tra sản phẩm và hoàn tất thanh toán (nếu chọn COD).</p>
                                    <ul class="step-tips">
                                        <li>Kiểm tra sản phẩm trước khi nhận</li>
                                        <li>Lưu hóa đơn để bảo hành sau này</li>
                                        <li>Đánh giá sản phẩm sau khi sử dụng</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            

                <!-- Shopping Tips -->
                <section class="shopping-tips-section">
                    <div class="container">
                        <div class="section-header">
                            <h2 class="section-title">Mẹo mua sắm thông minh</h2>
                            <p class="section-subtitle">Những lưu ý quan trọng khi mua hàng online</p>
                        </div>
                        
                        <div class="tips-grid">
                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-ruler"></i>
                                </div>
                                <h3>Chọn đúng size</h3>
                                <p>Luôn tham khảo bảng size, đo số đo cơ thể và đọc review của khách hàng trước khi chọn size.</p>
                            </div>

                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <h3>Săn sale hiệu quả</h3>
                                <p>Theo dõi lịch sale, thêm sản phẩm yêu thích vào wishlist để nhận thông báo giảm giá.</p>
                            </div>

                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3>Kiểm tra chất lượng</h3>
                                <p>Đọc kỹ mô tả sản phẩm, xem hình ảnh thực tế và kiểm tra chính sách đổi trả.</p>
                            </div>

                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <h3>Tối ưu vận chuyển</h3>
                                <p>Nhập đúng địa chỉ, chọn thời gian nhận hàng phù hợp để nhận hàng nhanh nhất.</p>
                            </div>

                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <h3>Để lại đánh giá</h3>
                                <p>Đánh giá sản phẩm sau khi mua giúp cộng đồng mua sắm tốt hơn và nhận điểm thưởng.</p>
                            </div>

                            <div class="tip-card">
                                <div class="tip-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h3>Liên hệ hỗ trợ</h3>
                                <p>Đừng ngần ngại liên hệ hotline 1900-1234 khi cần tư vấn hoặc gặp vấn đề.</p>
                            </div>
                        </div>
                    </div>
                </section>

                
            </div>
        </div>
    </div>
</div>
