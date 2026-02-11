<?php
// User Orders Edit - Information Page (Orders Cannot Be Edited)
// Load fake data
$dataFile = __DIR__ . '/../data/user_fake_data.json';
$data = [];

if (file_exists($dataFile)) {
    $jsonContent = file_get_contents($dataFile);
    $data = json_decode($jsonContent, true) ?: [];
}

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

// Find the specific order
$order = null;
foreach ($data['orders'] ?? [] as $orderItem) {
    if ($orderItem['id'] === $orderId) {
        $order = $orderItem;
        break;
    }
}

// Redirect if order not found
if (!$order) {
    header('Location: ?page=users&module=orders');
    exit;
}

// Status and type mappings
$statusLabels = [
    'completed' => 'Hoàn thành',
    'processing' => 'Đang xử lý',
    'pending' => 'Chờ xử lý',
    'cancelled' => 'Đã hủy'
];

$typeLabels = [
    'data_nguon_hang' => 'Data Nguồn Hàng',
    'van_chuyen' => 'Vận Chuyển',
    'dich_vu_tt' => 'Dịch Vụ Thanh Toán',
    'danh_hang' => 'Đánh Hàng',
    'khoa_hoc' => 'Khóa Học',
    'tool' => 'Tool'
];
?>

<div class="user-content-with-sidebar">
    <!-- User Sidebar -->
    <?php include 'app/views/_layout/user_sidebar.php'; ?>
    
    <!-- Order Edit Information Content -->
    <div class="user-orders">
        <!-- Header -->
        <div class="order-edit-info-header">
            <div class="order-edit-info-header-left">
                <div class="order-breadcrumb">
                    <a href="?page=users&module=orders">Đơn hàng</a>
                    <i class="fas fa-chevron-right"></i>
                    <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>">Chi tiết #<?php echo htmlspecialchars($order['id']); ?></a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Chỉnh sửa</span>
                </div>
                <h1>Không thể chỉnh sửa đơn hàng</h1>
                <p>Đơn hàng #<?php echo htmlspecialchars($order['id']); ?> không thể được chỉnh sửa</p>
            </div>
        </div>

        <!-- Information Notice -->
        <div class="edit-restriction-notice">
            <div class="edit-restriction-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="edit-restriction-content">
                <h3>Đơn hàng không thể chỉnh sửa</h3>
                <p>Để đảm bảo tính toàn vẹn và bảo mật của hệ thống, chúng tôi không cho phép chỉnh sửa đơn hàng sau khi đã được tạo.</p>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="order-edit-info-card">
            <div class="order-card-header">
                <h3>Thông tin đơn hàng</h3>
            </div>
            <div class="order-card-content">
                <div class="order-summary-grid">
                    <div class="order-summary-item">
                        <div class="order-summary-label">Mã đơn hàng:</div>
                        <div class="order-summary-value">#<?php echo htmlspecialchars($order['id']); ?></div>
                    </div>
                    
                    <div class="order-summary-item">
                        <div class="order-summary-label">Sản phẩm:</div>
                        <div class="order-summary-value"><?php echo htmlspecialchars($order['product_name']); ?></div>
                    </div>
                    
                    <div class="order-summary-item">
                        <div class="order-summary-label">Loại:</div>
                        <div class="order-summary-value"><?php echo $typeLabels[$order['type']] ?? $order['type']; ?></div>
                    </div>
                    
                    <div class="order-summary-item">
                        <div class="order-summary-label">Trạng thái:</div>
                        <div class="order-summary-value">
                            <span class="orders-badge orders-badge-<?php 
                                echo $order['status'] === 'completed' ? 'success' : 
                                    ($order['status'] === 'processing' ? 'warning' : 
                                    ($order['status'] === 'cancelled' ? 'danger' : 'info')); 
                            ?>">
                                <?php echo $statusLabels[$order['status']] ?? $order['status']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-summary-item">
                        <div class="order-summary-label">Ngày đặt:</div>
                        <div class="order-summary-value"><?php echo date('d/m/Y H:i', strtotime($order['date'])); ?></div>
                    </div>
                    
                    <div class="order-summary-item">
                        <div class="order-summary-label">Số tiền:</div>
                        <div class="order-summary-value order-amount"><?php echo number_format($order['amount'], 0, ',', '.'); ?> VNĐ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Actions -->
        <div class="order-edit-info-card">
            <div class="order-card-header">
                <h3>Các hành động có thể thực hiện</h3>
            </div>
            <div class="order-card-content">
                <div class="available-actions">
                    <?php if ($order['status'] === 'processing' || $order['status'] === 'pending'): ?>
                    <div class="action-item">
                        <div class="action-icon action-icon-cancel">
                            <i class="fas fa-times"></i>
                        </div>
                        <div class="action-content">
                            <h4>Hủy đơn hàng</h4>
                            <p>Bạn có thể hủy đơn hàng này nếu chưa được xử lý hoàn tất</p>
                            <a href="?page=users&module=orders&action=delete&id=<?php echo $order['id']; ?>" 
                               class="action-btn action-btn-cancel">
                                Hủy đơn hàng
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="action-item">
                        <div class="action-icon action-icon-support">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="action-content">
                            <h4>Liên hệ hỗ trợ</h4>
                            <p>Cần thay đổi thông tin đơn hàng? Liên hệ với đội ngũ hỗ trợ của chúng tôi</p>
                            <a href="?page=contact" class="action-btn action-btn-support">
                                Liên hệ hỗ trợ
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($order['status'] === 'completed'): ?>
                    <div class="action-item">
                        <div class="action-icon action-icon-reorder">
                            <i class="fas fa-redo"></i>
                        </div>
                        <div class="action-content">
                            <h4>Đặt lại đơn hàng</h4>
                            <p>Tạo đơn hàng mới với cùng sản phẩm</p>
                            <button onclick="handleReorder('<?php echo $order['id']; ?>')" 
                                    class="action-btn action-btn-reorder">
                                Đặt lại
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="action-item">
                        <div class="action-icon action-icon-view">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="action-content">
                            <h4>Xem chi tiết đơn hàng</h4>
                            <p>Xem thông tin đầy đủ và lịch sử của đơn hàng</p>
                            <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>" 
                               class="action-btn action-btn-view">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="order-edit-info-card">
            <div class="order-card-header">
                <h3>Câu hỏi thường gặp</h3>
            </div>
            <div class="order-card-content">
                <div class="faq-section">
                    <div class="faq-item">
                        <h4>Tại sao tôi không thể chỉnh sửa đơn hàng?</h4>
                        <p>Để đảm bảo tính chính xác và bảo mật, đơn hàng không thể được chỉnh sửa sau khi tạo. Điều này giúp tránh nhầm lẫn và đảm bảo quy trình xử lý đơn hàng diễn ra suôn sẻ.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Tôi muốn thay đổi thông tin đơn hàng thì phải làm sao?</h4>
                        <p>Nếu bạn cần thay đổi thông tin, vui lòng liên hệ với đội ngũ hỗ trợ khách hàng. Chúng tôi sẽ hỗ trợ bạn trong khả năng có thể.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Khi nào tôi có thể hủy đơn hàng?</h4>
                        <p>Bạn có thể hủy đơn hàng khi đơn hàng đang ở trạng thái "Chờ xử lý" hoặc "Đang xử lý". Sau khi hoàn thành, đơn hàng không thể hủy.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="order-edit-info-footer">
            <a href="?page=users&module=orders" class="orders-btn orders-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
            
            <a href="?page=users&module=orders&action=view&id=<?php echo $order['id']; ?>" 
               class="orders-btn orders-btn-primary">
                <i class="fas fa-eye"></i>
                Xem chi tiết đơn hàng
            </a>
        </div>
    </div>
</div>

<!-- Include Orders JavaScript -->
<script src="assets/js/user_orders.js"></script>
<script>
// Handle reorder functionality
function handleReorder(orderId) {
    if (confirm('Bạn có muốn thêm sản phẩm này vào giỏ hàng?')) {
        // Show loading state
        const button = event.target;
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        button.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            alert('Đã thêm sản phẩm vào giỏ hàng thành công!');
            
            // Reset button
            button.innerHTML = originalContent;
            button.disabled = false;
            
            // Redirect to cart
            window.location.href = '?page=users&module=cart';
        }, 1000);
    }
}
</script>