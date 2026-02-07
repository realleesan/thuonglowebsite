<?php
// Get affiliate ID from URL
$affiliate_id = (int)($_GET['id'] ?? 0);

// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$affiliates = $fake_data['affiliates'];
$users = $fake_data['users'];
$orders = $fake_data['orders'];

// Find affiliate
$affiliate = null;
foreach ($affiliates as $item) {
    if ($item['id'] == $affiliate_id) {
        $affiliate = $item;
        break;
    }
}

// If affiliate not found, redirect
if (!$affiliate) {
    header('Location: ?page=admin&module=affiliates&error=not_found');
    exit;
}

// Find user info
$user = null;
foreach ($users as $item) {
    if ($item['id'] == $affiliate['user_id']) {
        $user = $item;
        break;
    }
}

// Get affiliate orders (demo data)
$affiliate_orders = array_filter($orders, function($order) use ($affiliate) {
    // In real app, this would check referral tracking
    return rand(0, 1); // Random for demo
});

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
$monthly_orders = 0; // Demo
$monthly_sales = 0; // Demo
$monthly_commission = 0; // Demo

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
                    data-id="<?= $affiliate_id ?>" data-name="<?= htmlspecialchars($user['name'] ?? 'N/A') ?>">
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
                        <h4><?= htmlspecialchars($user['name'] ?? 'N/A') ?></h4>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
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
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= formatPrice($affiliate['total_sales']) ?></h4>
                            <p>Tổng Doanh Số</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= formatPrice($affiliate['total_commission']) ?></h4>
                            <p>Tổng Hoa Hồng</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?= $total_orders ?></h4>
                            <p>Tổng Đơn Hàng</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h4>0</h4>
                            <p>Khách Giới Thiệu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="charts-section">
        <div class="chart-container">
            <div class="chart-header">
                <h3>Biểu Đồ Doanh Số & Hoa Hồng</h3>
                <div class="chart-controls">
                    <select id="chartPeriod">
                        <option value="6months">6 tháng gần đây</option>
                        <option value="12months">12 tháng gần đây</option>
                        <option value="year">Theo năm</option>
                    </select>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="performanceChart" width="400" height="200"></canvas>
            </div>
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
                                <td>
                                    <?php
                                    $customer = null;
                                    foreach ($users as $u) {
                                        if ($u['id'] == $order['user_id']) {
                                            $customer = $u;
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($customer['name'] ?? 'N/A');
                                    ?>
                                </td>
                                <td>Sản phẩm #<?= $order['product_id'] ?></td>
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
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xác nhận xóa</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đại lý <strong id="deleteAffiliateName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác và sẽ xóa tất cả dữ liệu liên quan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
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
        interaction: {
            mode: 'index',
            intersect: false,
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

// Delete functionality
document.querySelector('.delete-btn').addEventListener('click', function() {
    const id = this.dataset.id;
    const name = this.dataset.name;
    
    document.getElementById('deleteAffiliateName').textContent = name;
    document.getElementById('deleteModal').style.display = 'block';
    
    document.getElementById('confirmDelete').onclick = function() {
        // In real app: send delete request
        alert('Đã xóa đại lý: ' + name + ' (Demo)');
        window.location.href = '?page=admin&module=affiliates';
    };
});

// Modal close functionality
document.querySelector('.modal-close').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'none';
});

document.getElementById('cancelDelete').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>