<?php
/**
 * Admin Affiliates View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get affiliate ID from URL
    $affiliate_id = (int)($_GET['id'] ?? 0);
    
    if (!$affiliate_id) {
        header('Location: ?page=admin&module=affiliates&error=invalid_id');
        exit;
    }
    
    // Get affiliate data using AdminService
    $affiliateData = $service->getAffiliateDetailsData($affiliate_id);
    $affiliate = $affiliateData['affiliate'];
    $affiliate_orders = $affiliateData['orders'];
    $performance_data = $affiliateData['performance_data'];
    
    // Redirect if affiliate not found
    if (!$affiliate) {
        header('Location: ?page=admin&module=affiliates&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Affiliates View Error', $e);
    header('Location: ?page=admin&module=affiliates&error=system_error');
    exit;
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Calculate stats
$total_orders = count($affiliate_orders);
$monthly_orders = 0;
$monthly_sales = 0;
$monthly_commission = 0;

// Generate sample performance data for charts
$performance_data = [
    'labels' => ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
    'sales' => [5000000, 7500000, 12000000, 8500000, 15000000, 18000000],
    'commission' => [500000, 750000, 1200000, 850000, 1500000, 1800000]
];
?>
<div class="affiliates-page affiliates-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Chi Tiết Đại Lý
            </h1>
            <p class="page-description">Thông tin chi tiết và thống kê của đại lý</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=affiliates&action=edit&id=<?= $affiliate_id ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn" 
                    data-id="<?= $affiliate_id ?>" data-name="<?= htmlspecialchars($affiliate['user_name'] ?? 'N/A') ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=affiliates" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Affiliate Info Cards -->
    <div class="info-cards-grid">
        <div class="info-card primary">
            <div class="card-header">
                <h3>Thông Tin Cơ Bản</h3>
                <span class="status-badge status-<?= $affiliate['status'] ?>">
                    <?php
                    switch($affiliate['status']) {
                        case 'active': echo 'Hoạt động'; break;
                        case 'inactive': echo 'Không hoạt động'; break;
                        case 'pending': echo 'Chờ duyệt'; break;
                        default: echo 'N/A';
                    }
                    ?>
                </span>
            </div>
            <div class="card-body">
                <div class="user-profile">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <h4><?= htmlspecialchars($affiliate['user_name'] ?? 'N/A') ?></h4>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($affiliate['user_email'] ?? 'N/A') ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($affiliate['user_phone'] ?? 'N/A') ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($affiliate['user_address'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div class="affiliate-details">
                    <div class="detail-row">
                        <label>ID Đại Lý:</label>
                        <span>#<?= $affiliate['id'] ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Mã Giới Thiệu:</label>
                        <span class="referral-code"><?= htmlspecialchars($affiliate['referral_code']) ?></span>
                        <button type="button" class="btn btn-sm btn-outline" onclick="copyReferralCode()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="detail-row">
                        <label>Tỷ Lệ Hoa Hồng:</label>
                        <span class="commission-rate"><?= $affiliate['commission_rate'] ?>%</span>
                    </div>
                    <div class="detail-row">
                        <label>Ngày Tham Gia:</label>
                        <span><?= formatDate($affiliate['created_at']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">
                <h3>Thống Kê Tổng Quan</h3>
            </div>
            <div class="card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= formatPrice($affiliate['total_sales']) ?></div>
                            <div class="stat-label">Tổng Doanh Số</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= formatPrice($affiliate['total_commission']) ?></div>
                            <div class="stat-label">Tổng Hoa Hồng</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $total_orders ?></div>
                            <div class="stat-label">Tổng Đơn Hàng</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Khách Giới Thiệu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Recent Orders Grid -->
    <div class="charts-orders-grid">
        <!-- Performance Charts -->
        <div class="charts-section">
            <div class="chart-header">
                <h3>Biểu Đồ Doanh Số & Hoa Hồng</h3>
                <select id="chartPeriod">
                    <option value="6months">6 tháng gần đây</option>
                    <option value="12months">12 tháng gần đây</option>
                    <option value="year">Theo năm</option>
                </select>
            </div>
            <div class="chart-body">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders-section">
        <div class="section-header">
            <h3>Đơn Hàng Gần Đây</h3>
            <a href="?page=admin&module=orders&affiliate=<?= $affiliate_id ?>" class="btn btn-outline">
                <i class="fas fa-external-link-alt"></i>
                Xem tất cả
            </a>
        </div>
        
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Hoa hồng</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($affiliate_orders)): ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                <i class="fas fa-inbox"></i>
                                <p>Chưa có đơn hàng nào từ đại lý này</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($affiliate_orders, 0, 5) as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                <td>Sản phẩm #<?= $order['product_id'] ?? 'N/A' ?></td>
                                <td><?= formatPrice($order['total']) ?></td>
                                <td><?= formatPrice($order['total'] * $affiliate['commission_rate'] / 100) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?php
                                        switch($order['status']) {
                                            case 'pending': echo 'Chờ xử lý'; break;
                                            case 'processing': echo 'Đang xử lý'; break;
                                            case 'completed': echo 'Hoàn thành'; break;
                                            case 'cancelled': echo 'Đã hủy'; break;
                                            default: echo 'N/A';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?= formatDate($order['created_at']) ?></td>
                                <td>
                                    <a href="?page=admin&module=orders&action=view&id=<?= $order['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <!-- Referral Links -->
    <div class="referral-section">
        <div class="section-header">
            <h3>Liên Kết Giới Thiệu</h3>
        </div>
        
        <div class="referral-links">
            <div class="link-item">
                <label>Link chính:</label>
                <div class="link-preview">
                    <code id="mainReferralLink">https://thuonglo.com/?ref=<?= htmlspecialchars($affiliate['referral_code']) ?></code>
                    <button type="button" class="btn btn-sm btn-outline" onclick="copyLink('mainReferralLink')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            
            <div class="link-item">
                <label>Link sản phẩm:</label>
                <div class="link-preview">
                    <code id="productReferralLink">https://thuonglo.com/products?ref=<?= htmlspecialchars($affiliate['referral_code']) ?></code>
                    <button type="button" class="btn btn-sm btn-outline" onclick="copyLink('productReferralLink')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="productDeleteModal" style="display: none;">
        <div class="product-modal-overlay"></div>
        <div class="product-modal-container">
            <div class="product-modal-header">
                <h3>Xác nhận xóa</h3>
                <button class="product-modal-close" onclick="closeProductDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa danh mục "<strong id="productDeleteName"></strong>"?</p>
                <p class="product-modal-warning">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="product-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeProductDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" id="prConfirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>

    <style>
    #productDeleteModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 999999;
    }

    .product-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
    }

    .product-modal-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
    }

    .product-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .product-modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }

    .product-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
    }

    .product-modal-close:hover {
        color: #374151;
        background: #f3f4f6;
    }

    .product-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 12px 12px;
    }

    .product-modal-warning {
        color: #dc2626 !important;
        font-size: 13px;
        font-weight: 500;
    }
    
    /* Fix for charts and recent orders height */
    .affiliates-view-page .charts-section {
        min-height: 350px !important;
        height: 350px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .affiliates-view-page .recent-orders-section {
        min-height: 350px !important;
        height: 350px;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Fix grid width */
    .affiliates-view-page .charts-orders-grid {
        width: 100%;
        box-sizing: border-box;
    }
    
    .affiliates-view-page .info-cards-grid {
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Ensure all children use border-box */
    .affiliates-view-page .charts-orders-grid *,
    .affiliates-view-page .info-cards-grid * {
        box-sizing: border-box;
    }
    
    /* Force exact equal sizing for all 4 blocks */
    .affiliates-view-page .info-cards-grid,
    .affiliates-view-page .charts-orders-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 20px;
    }
    
    .affiliates-view-page .info-card,
    .affiliates-view-page .charts-section,
    .affiliates-view-page .recent-orders-section {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 16px !important;
        height: 450px !important;
        min-height: 450px !important;
        background: #ffffff !important;
        border: 1px solid #E5E7EB !important;
        box-shadow: none !important;
    }
    
    /* Chart section simplified styling */
    .affiliates-view-page .charts-section .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .affiliates-view-page .charts-section .chart-header h3 {
        margin: 0;
        font-size: 16px;
    }
    
    .affiliates-view-page .charts-section .chart-header select {
        padding: 6px 10px;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        font-size: 13px;
    }
    
    .affiliates-view-page .charts-section .chart-body {
        height: calc(450px - 60px);
        min-height: 200px;
        position: relative;
    }
    
    .affiliates-view-page .charts-section .chart-body canvas {
        width: 100% !important;
        height: 100% !important;
    }
    
    /* Referral section white frame */
    .affiliates-view-page .referral-section {
        background: #ffffff !important;
        border: 1px solid #E5E7EB !important;
        border-radius: 8px !important;
        padding: 16px !important;
        box-shadow: none !important;
    }
    </style>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($performance_data['labels']) ?>,
        datasets: [{
            label: 'Doanh số (VNĐ)',
            data: <?= json_encode($performance_data['sales']) ?>,
            borderColor: '#356DF1',
            backgroundColor: 'rgba(53, 109, 241, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: 'Hoa hồng (VNĐ)',
            data: <?= json_encode($performance_data['commission']) ?>,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Tháng'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Doanh số (VNĐ)'
                },
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value);
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Hoa hồng (VNĐ)'
                },
                grid: {
                    drawOnChartArea: false,
                },
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value);
                    }
                }
            },
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + 
                               new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                    }
                }
            }
        }
    }
});

// Copy functions
function copyReferralCode() {
    const code = '<?= $affiliate['referral_code'] ?>';
    navigator.clipboard.writeText(code).then(() => {
        alert('Đã sao chép mã giới thiệu: ' + code);
    });
}

function copyLink(elementId) {
    const link = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(link).then(() => {
        alert('Đã sao chép link giới thiệu!');
    });
}

// Delete button click handler
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const affiliateId = this.getAttribute('data-id');
            const affiliateName = this.getAttribute('data-name');
            
            const nameElement = document.getElementById('productDeleteName');
            if (nameElement) {
                nameElement.textContent = affiliateName || 'đại lý này';
            }
            
            const modal = document.getElementById('productDeleteModal');
            if (modal) {
                modal.dataset.deleteId = affiliateId;
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Confirm delete button
    const confirmDeleteBtn = document.getElementById('prConfirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const modal = document.getElementById('productDeleteModal');
            const deleteId = modal ? modal.dataset.deleteId : null;
            if (deleteId) {
                window.location.href = '?page=admin&module=affiliates&action=delete&id=' + deleteId;
            }
        });
    }
    
    // Close modal on overlay click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('product-modal-overlay')) {
            closeProductDeleteModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('productDeleteModal');
            if (modal && modal.style.display === 'block') {
                closeProductDeleteModal();
            }
        }
    });
});

function closeProductDeleteModal() {
    const modal = document.getElementById('productDeleteModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        delete modal.dataset.deleteId;
    }
}
</script>