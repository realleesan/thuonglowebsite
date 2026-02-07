<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$settings = $fake_data['settings'];

// Get setting key from URL
$setting_key = $_GET['key'] ?? '';

// Find the setting
$setting = null;
foreach ($settings as $s) {
    if ($s['key'] === $setting_key) {
        $setting = $s;
        break;
    }
}

// If setting not found, redirect
if (!$setting) {
    header('Location: ?page=admin&module=settings&error=not_found');
    exit;
}

// Format functions
function formatSettingType($type) {
    $types = [
        'text' => 'Văn bản',
        'textarea' => 'Văn bản dài',
        'email' => 'Email',
        'url' => 'URL',
        'number' => 'Số',
        'boolean' => 'Đúng/Sai',
        'select' => 'Lựa chọn',
        'file' => 'Tệp tin'
    ];
    return $types[$type] ?? ucfirst($type);
}

function formatSettingValue($value, $type) {
    switch ($type) {
        case 'boolean':
            return $value ? 'Có (True)' : 'Không (False)';
        case 'number':
            return number_format($value);
        case 'email':
            return '<a href="mailto:' . htmlspecialchars($value) . '">' . htmlspecialchars($value) . '</a>';
        case 'url':
            return '<a href="' . htmlspecialchars($value) . '" target="_blank">' . htmlspecialchars($value) . ' <i class="fas fa-external-link-alt"></i></a>';
        default:
            return htmlspecialchars($value);
    }
}

function getTypeIcon($type) {
    $icons = [
        'text' => 'fas fa-font',
        'textarea' => 'fas fa-align-left',
        'email' => 'fas fa-envelope',
        'url' => 'fas fa-link',
        'number' => 'fas fa-hashtag',
        'boolean' => 'fas fa-toggle-on',
        'select' => 'fas fa-list',
        'file' => 'fas fa-file'
    ];
    return $icons[$type] ?? 'fas fa-cog';
}

// Demo usage data
$usage_count = rand(50, 500);
$last_modified = date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'));
?>

<div class="settings-page settings-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-eye"></i>
                Chi Tiết Cài Đặt
            </h1>
            <p class="page-description">Thông tin chi tiết cài đặt: <strong><?= htmlspecialchars($setting['key']) ?></strong></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=settings&action=edit&key=<?= urlencode($setting['key']) ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteSetting('<?= htmlspecialchars($setting['key']) ?>', '<?= htmlspecialchars($setting['description']) ?>')">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=settings" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Setting Overview -->
    <div class="setting-overview">
        <div class="setting-overview-grid">
            <!-- Setting Icon -->
            <div class="setting-icon-section">
                <div class="setting-icon-main">
                    <i class="<?= getTypeIcon($setting['type']) ?>"></i>
                </div>
                <div class="setting-type-info">
                    <span class="type-badge type-<?= $setting['type'] ?>">
                        <?= formatSettingType($setting['type']) ?>
                    </span>
                </div>
            </div>

            <!-- Setting Info -->
            <div class="setting-info-section">
                <div class="setting-header">
                    <h2 class="setting-key"><?= htmlspecialchars($setting['key']) ?></h2>
                    <div class="setting-badges">
                        <span class="badge badge-primary">Hệ thống</span>
                        <span class="badge badge-success">Hoạt động</span>
                    </div>
                </div>

                <div class="setting-meta">
                    <div class="meta-item">
                        <span class="meta-label">Mô tả:</span>
                        <span class="meta-value"><?= htmlspecialchars($setting['description']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Loại dữ liệu:</span>
                        <span class="meta-value">
                            <span class="type-badge type-<?= $setting['type'] ?>">
                                <i class="<?= getTypeIcon($setting['type']) ?>"></i>
                                <?= formatSettingType($setting['type']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Giá trị hiện tại:</span>
                        <div class="meta-value setting-value-display">
                            <?= formatSettingValue($setting['value'], $setting['type']) ?>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Lần sử dụng:</span>
                        <span class="meta-value"><?= number_format($usage_count) ?> lần</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Cập nhật cuối:</span>
                        <span class="meta-value"><?= date('d/m/Y H:i', strtotime($last_modified)) ?></span>
                    </div>
                </div>

                <!-- Setting Stats -->
                <div class="setting-stats">
                    <h4>Thống Kê Sử Dụng:</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= number_format($usage_count) ?></div>
                                <div class="stat-label">Lần truy cập</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= rand(1, 10) ?></div>
                                <div class="stat-label">Lần chỉnh sửa</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= rand(1, 365) ?></div>
                                <div class="stat-label">Ngày hoạt động</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">100%</div>
                                <div class="stat-label">Độ tin cậy</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Information Tabs -->
    <div class="setting-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="details">Chi Tiết</button>
            <button class="tab-btn" data-tab="usage">Sử Dụng</button>
            <button class="tab-btn" data-tab="validation">Xác Thực</button>
            <button class="tab-btn" data-tab="history">Lịch Sử</button>
        </div>

        <div class="tabs-content">
            <!-- Details Tab -->
            <div class="tab-content active" id="details">
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông Tin Cài Đặt</h4>
                        <table class="details-table">
                            <tr>
                                <td><strong>Tên cài đặt (Key):</strong></td>
                                <td><code><?= htmlspecialchars($setting['key']) ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Mô tả:</strong></td>
                                <td><?= htmlspecialchars($setting['description']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Loại dữ liệu:</strong></td>
                                <td>
                                    <span class="type-badge type-<?= $setting['type'] ?>">
                                        <i class="<?= getTypeIcon($setting['type']) ?>"></i>
                                        <?= formatSettingType($setting['type']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Giá trị:</strong></td>
                                <td class="setting-value-cell">
                                    <?php if ($setting['type'] === 'textarea'): ?>
                                        <div class="textarea-value">
                                            <?= nl2br(htmlspecialchars($setting['value'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <?= formatSettingValue($setting['value'], $setting['type']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Độ dài giá trị:</strong></td>
                                <td><?= strlen($setting['value']) ?> ký tự</td>
                            </tr>
                        </table>
                    </div>

                    <div class="details-section">
                        <h4>Thông Tin Kỹ Thuật</h4>
                        <table class="details-table">
                            <tr>
                                <td><strong>Định dạng lưu trữ:</strong></td>
                                <td><?= strtoupper($setting['type']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Có thể null:</strong></td>
                                <td><span class="badge badge-danger">Không</span></td>
                            </tr>
                            <tr>
                                <td><strong>Có thể chỉnh sửa:</strong></td>
                                <td><span class="badge badge-success">Có</span></td>
                            </tr>
                            <tr>
                                <td><strong>Tự động tải:</strong></td>
                                <td><span class="badge badge-success">Có</span></td>
                            </tr>
                            <tr>
                                <td><strong>Cache:</strong></td>
                                <td><span class="badge badge-info">1 giờ</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Usage Tab -->
            <div class="tab-content" id="usage">
                <div class="usage-section">
                    <h4>Cách Sử Dụng Trong Code</h4>
                    
                    <div class="code-examples">
                        <div class="code-example">
                            <h5>PHP:</h5>
                            <div class="code-block">
                                <code>
$value = getSetting('<?= $setting['key'] ?>');<br>
echo $value; // <?= htmlspecialchars($setting['value']) ?>
                                </code>
                            </div>
                        </div>

                        <div class="code-example">
                            <h5>JavaScript:</h5>
                            <div class="code-block">
                                <code>
const value = window.settings['<?= $setting['key'] ?>'];<br>
console.log(value); // "<?= htmlspecialchars($setting['value']) ?>"
                                </code>
                            </div>
                        </div>

                        <div class="code-example">
                            <h5>Template:</h5>
                            <div class="code-block">
                                <code>
&lt;?= getSetting('<?= $setting['key'] ?>') ?&gt;<br>
// Hiển thị: <?= htmlspecialchars($setting['value']) ?>
                                </code>
                            </div>
                        </div>
                    </div>

                    <div class="usage-stats">
                        <h4>Thống Kê Sử Dụng</h4>
                        <div class="usage-chart">
                            <div class="chart-placeholder">
                                <i class="fas fa-chart-line"></i>
                                <p>Biểu đồ sử dụng theo thời gian (Demo)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Tab -->
            <div class="tab-content" id="validation">
                <div class="validation-section">
                    <h4>Quy Tắc Xác Thực</h4>
                    
                    <div class="validation-rules">
                        <?php
                        $rules = [];
                        switch ($setting['type']) {
                            case 'email':
                                $rules = [
                                    'Phải là địa chỉ email hợp lệ',
                                    'Không được để trống',
                                    'Tối đa 255 ký tự'
                                ];
                                break;
                            case 'url':
                                $rules = [
                                    'Phải là URL hợp lệ',
                                    'Bắt đầu bằng http:// hoặc https://',
                                    'Không được để trống'
                                ];
                                break;
                            case 'number':
                                $rules = [
                                    'Phải là số hợp lệ',
                                    'Có thể là số thập phân',
                                    'Không được để trống'
                                ];
                                break;
                            case 'boolean':
                                $rules = [
                                    'Chỉ chấp nhận true/false hoặc 1/0',
                                    'Không được để trống'
                                ];
                                break;
                            case 'textarea':
                                $rules = [
                                    'Có thể chứa nhiều dòng',
                                    'Tối đa 65,535 ký tự',
                                    'Không được để trống'
                                ];
                                break;
                            default:
                                $rules = [
                                    'Chuỗi văn bản',
                                    'Tối đa 255 ký tự',
                                    'Không được để trống'
                                ];
                        }
                        ?>
                        
                        <ul class="rules-list">
                            <?php foreach ($rules as $rule): ?>
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <?= $rule ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="validation-test">
                        <h4>Kiểm Tra Giá Trị Hiện Tại</h4>
                        <div class="test-result">
                            <div class="test-item">
                                <span class="test-label">Định dạng:</span>
                                <span class="test-status success">
                                    <i class="fas fa-check-circle"></i>
                                    Hợp lệ
                                </span>
                            </div>
                            <div class="test-item">
                                <span class="test-label">Độ dài:</span>
                                <span class="test-status success">
                                    <i class="fas fa-check-circle"></i>
                                    <?= strlen($setting['value']) ?> ký tự
                                </span>
                            </div>
                            <div class="test-item">
                                <span class="test-label">Không rỗng:</span>
                                <span class="test-status success">
                                    <i class="fas fa-check-circle"></i>
                                    Có giá trị
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div class="tab-content" id="history">
                <div class="history-section">
                    <h4>Lịch Sử Thay Đổi</h4>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Tạo cài đặt</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i', strtotime('-30 days')) ?></span>
                                </div>
                                <p>Cài đặt được tạo với giá trị ban đầu</p>
                                <div class="timeline-details">
                                    <code>Giá trị: "<?= htmlspecialchars($setting['value']) ?>"</code>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (rand(0, 1)): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Cập nhật giá trị</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i', strtotime($last_modified)) ?></span>
                                </div>
                                <p>Giá trị được cập nhật bởi Admin</p>
                                <div class="timeline-details">
                                    <code>Từ: "Giá trị cũ" → "<?= htmlspecialchars($setting['value']) ?>"</code>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Cập nhật mô tả</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i', strtotime('-10 days')) ?></span>
                                </div>
                                <p>Mô tả được cập nhật để rõ ràng hơn</p>
                                <div class="timeline-details">
                                    <code>Mô tả: "<?= htmlspecialchars($setting['description']) ?>"</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Xác nhận xóa cài đặt</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa cài đặt <strong id="deleteSettingKey"></strong>?</p>
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <p><strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!</p>
                    <p>Cài đặt sẽ bị xóa khỏi hệ thống và có thể ảnh hưởng đến hoạt động của website.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">Xóa cài đặt</button>
        </div>
    </div>
</div>

<script>
function deleteSetting(key, description) {
    document.getElementById('deleteSettingKey').textContent = key;
    document.getElementById('deleteModal').style.display = 'block';
}

// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all tabs and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // Modal functionality
    const modal = document.getElementById('deleteModal');
    const closeBtn = document.querySelector('.modal-close');
    const cancelBtn = document.getElementById('cancelDelete');
    const confirmBtn = document.getElementById('confirmDelete');
    
    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    cancelBtn.addEventListener('click', () => modal.style.display = 'none');
    
    confirmBtn.addEventListener('click', function() {
        // Demo delete action
        alert('Demo: Cài đặt đã được xóa (không thực sự xóa)');
        modal.style.display = 'none';
        // In real app: redirect to settings list
        // window.location.href = '?page=admin&module=settings&success=deleted';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>