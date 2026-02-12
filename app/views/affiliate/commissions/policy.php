<?php
/**
 * Affiliate Commissions Policy
 * Chính sách hoa hồng trọn đời (Lifetime Commission)
 */

// 1. Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// 2. Chọn service (ưu tiên biến được inject từ routing)
$service = isset($currentService) ? $currentService : ($adminService ?? $publicService ?? null);

// Initialize data variables
$policy = [
    'commission_rate' => 10,
    'min_withdrawal' => 100000,
    'payment_schedule' => 'monthly'
];

try {
    // Get current affiliate ID from session
    $affiliateId = $_SESSION['user_id'] ?? 1;
    
    // Get dashboard data FIRST for affiliate info (needed by header)
    if ($service && method_exists($service, 'getDashboardData')) {
        $dashboardData = $service->getDashboardData($affiliateId);
        $affiliateInfo = $dashboardData['affiliate'] ?? [
            'name' => '',
            'email' => ''
        ];
    }
    
    if ($service && method_exists($service, 'getSettings')) {
        // Get commission policy từ AdminService
        $settings = $service->getSettings();
        $policy = [
            'commission_rate' => $settings['commission_rate'] ?? 10,
            'min_withdrawal' => $settings['min_withdrawal'] ?? 100000,
            'payment_schedule' => $settings['payment_schedule'] ?? 'monthly'
        ];
    }
} catch (Exception $e) {
    $errorHandler->handleViewError($e, 'affiliate_commissions_policy', []);
    error_log("Commission Policy Error: " . $e->getMessage());
}

// Set page info cho master layout
$page_title = 'Chính sách hoa hồng';
$page_module = 'commissions';
$page_action = 'policy';

// Include master layout
ob_start();
?>
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-file-contract"></i>
        Chính sách hoa hồng
    </h1>
    <p class="page-subtitle">Tìm hiểu về cơ chế hoa hồng trọn đời và các mức hoa hồng</p>
</div>

<!-- Lifetime Commission Info -->
<div class="alert alert-info">
    <div class="alert-icon">
        <i class="fas fa-infinity"></i>
    </div>
    <div class="alert-content">
        <h4 class="alert-title">Hoa hồng trọn đời (Lifetime Commission)</h4>
        <p class="alert-text">
            <?php echo htmlspecialchars($policy['lifetime_info'] ?? ''); ?>
        </p>
    </div>
</div>

<!-- Commission Types -->
<div class="dashboard-section">
    <h2 class="section-title">
        <i class="fas fa-layer-group"></i>
        Các loại hoa hồng
    </h2>
    
    <div class="commission-types-grid">
        <?php if (isset($policy['commission_types'])): ?>
            <?php foreach ($policy['commission_types'] as $type): ?>
            <div class="commission-type-card">
                <div class="commission-type-icon commission-type-<?php echo $type['color']; ?>">
                    <?php if ($type['type'] === 'data_subscription'): ?>
                        <i class="fas fa-database"></i>
                    <?php else: ?>
                        <i class="fas fa-truck"></i>
                    <?php endif; ?>
                </div>
                <div class="commission-type-content">
                    <h3 class="commission-type-name"><?php echo htmlspecialchars($type['name']); ?></h3>
                    <p class="commission-type-description">
                        <?php echo htmlspecialchars($type['description']); ?>
                    </p>
                    <?php if ($type['type'] === 'data_subscription'): ?>
                        <div class="commission-type-badge badge-purple">
                            <i class="fas fa-sync-alt"></i>
                            Thu nhập thụ động
                        </div>
                    <?php else: ?>
                        <div class="commission-type-badge badge-orange">
                            <i class="fas fa-bolt"></i>
                            Thu nhập phát sinh
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Commission Tiers -->
<div class="dashboard-section">
    <h2 class="section-title">
        <i class="fas fa-chart-line"></i>
        Bảng mức hoa hồng theo cấp độ
    </h2>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-tiers">
                    <thead>
                        <tr>
                            <th>Cấp độ</th>
                            <th>Doanh số tối thiểu</th>
                            <th>Doanh số tối đa</th>
                            <th class="text-center">Tỷ lệ hoa hồng</th>
                            <th class="text-center">Ví dụ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($policy['tiers'])): ?>
                            <?php foreach ($policy['tiers'] as $tier): ?>
                            <tr class="tier-row">
                                <td>
                                    <div class="tier-badge" style="background-color: <?php echo $tier['color']; ?>;">
                                        <i class="fas fa-medal"></i>
                                        <?php echo htmlspecialchars($tier['name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="tier-amount">
                                        <?php echo number_format($tier['min_revenue']); ?>đ
                                    </span>
                                </td>
                                <td>
                                    <span class="tier-amount">
                                        <?php 
                                        if ($tier['max_revenue'] === null) {
                                            echo 'Không giới hạn';
                                        } else {
                                            echo number_format($tier['max_revenue']) . 'đ';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="tier-rate">
                                        <?php echo $tier['rate']; ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="tier-example">
                                        <?php 
                                        $exampleRevenue = 10000000; // 10 triệu
                                        $exampleCommission = $exampleRevenue * ($tier['rate'] / 100);
                                        echo number_format($exampleCommission) . 'đ';
                                        ?>
                                    </span>
                                    <div class="tier-example-note">
                                        (từ 10.000.000đ doanh số)
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="dashboard-section">
    <h2 class="section-title">
        <i class="fas fa-question-circle"></i>
        Cách thức hoạt động
    </h2>
    
    <div class="how-it-works-grid">
        <div class="how-it-works-card">
            <div class="how-it-works-number">1</div>
            <div class="how-it-works-content">
                <h3 class="how-it-works-title">Giới thiệu khách hàng</h3>
                <p class="how-it-works-text">
                    Chia sẻ link giới thiệu của bạn với khách hàng tiềm năng
                </p>
            </div>
        </div>

        <div class="how-it-works-card">
            <div class="how-it-works-number">2</div>
            <div class="how-it-works-content">
                <h3 class="how-it-works-title">Khách hàng đăng ký</h3>
                <p class="how-it-works-text">
                    Khách hàng đăng ký và mua sản phẩm/dịch vụ qua link của bạn
                </p>
            </div>
        </div>

        <div class="how-it-works-card">
            <div class="how-it-works-number">3</div>
            <div class="how-it-works-content">
                <h3 class="how-it-works-title">Nhận hoa hồng</h3>
                <p class="how-it-works-text">
                    Bạn nhận hoa hồng từ giao dịch đầu tiên và tất cả các giao dịch sau đó
                </p>
            </div>
        </div>

        <div class="how-it-works-card">
            <div class="how-it-works-number">4</div>
            <div class="how-it-works-content">
                <h3 class="how-it-works-title">Thu nhập thụ động</h3>
                <p class="how-it-works-text">
                    Tiếp tục nhận hoa hồng từ các lần gia hạn và mua thêm dịch vụ
                </p>
            </div>
        </div>
    </div>
</div>

<!-- FAQs -->
<div class="dashboard-section">
    <h2 class="section-title">
        <i class="fas fa-comments"></i>
        Câu hỏi thường gặp
    </h2>
    
    <div class="faqs-container">
        <div class="faq-item">
            <div class="faq-question">
                <i class="fas fa-question-circle"></i>
                Hoa hồng trọn đời có nghĩa là gì?
            </div>
            <div class="faq-answer">
                Bạn sẽ nhận hoa hồng từ tất cả các giao dịch của khách hàng do bạn giới thiệu, 
                không chỉ giao dịch đầu tiên mà còn cả các lần gia hạn, nâng cấp gói, 
                và mua thêm dịch vụ trong tương lai.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <i class="fas fa-question-circle"></i>
                Sự khác biệt giữa Gói Data và Vận chuyển?
            </div>
            <div class="faq-answer">
                <strong>Gói Data (Subscription):</strong> Thu nhập thụ động, lặp lại hàng tháng 
                từ các gói data subscription của khách hàng.<br>
                <strong>Vận chuyển (Logistics):</strong> Thu nhập phát sinh theo từng đơn hàng 
                vận chuyển mà khách hàng sử dụng.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <i class="fas fa-question-circle"></i>
                Khi nào tôi nhận được hoa hồng?
            </div>
            <div class="faq-answer">
                Hoa hồng sẽ được thanh toán vào ngày 5 hàng tháng cho các giao dịch 
                đã hoàn thành trong tháng trước đó.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <i class="fas fa-question-circle"></i>
                Làm thế nào để lên cấp độ cao hơn?
            </div>
            <div class="faq-answer">
                Cấp độ của bạn được tính dựa trên tổng doanh số tích lũy. 
                Khi đạt mốc doanh số của cấp độ tiếp theo, bạn sẽ tự động được nâng cấp 
                và áp dụng tỷ lệ hoa hồng mới.
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../_layout/affiliate_master.php';
?>
