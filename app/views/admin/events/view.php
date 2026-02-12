<?php
$service = isset($currentService) ? $currentService : ($adminService ?? null);

require_once __DIR__ . '/../../services/AdminService.php';
require_once __DIR__ . '/../../services/ErrorHandler.php';

try {
    // Get event ID
    $event_id = (int)($_GET['id'] ?? 0);
    
    if (!$event_id) {
        header('Location: ?page=admin&module=events');
        exit;
    }
    
    // Get event data from service
    $eventData = $service->getEventDetailsData($event_id);
    $current_event = $eventData['event'];
    
    // Redirect if event not found
    if (!$current_event) {
        header('Location: ?page=admin&module=events');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Events View Error', $e);
    header('Location: ?page=admin&module=events&error=1');
    exit;
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Calculate event duration
function getEventDuration($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $duration = $end_time - $start_time;
    
    $hours = floor($duration / 3600);
    $minutes = floor(($duration % 3600) / 60);
    
    if ($hours > 0) {
        return $hours . ' giờ ' . ($minutes > 0 ? $minutes . ' phút' : '');
    } else {
        return $minutes . ' phút';
    }
}

// Get event status info
function getEventStatusInfo($status) {
    switch($status) {
        case 'upcoming': return ['text' => 'Sắp diễn ra', 'class' => 'upcoming', 'icon' => 'clock'];
        case 'ongoing': return ['text' => 'Đang diễn ra', 'class' => 'ongoing', 'icon' => 'play'];
        case 'completed': return ['text' => 'Đã kết thúc', 'class' => 'completed', 'icon' => 'check'];
        case 'cancelled': return ['text' => 'Đã hủy', 'class' => 'cancelled', 'icon' => 'times'];
        default: return ['text' => ucfirst($status), 'class' => $status, 'icon' => 'question'];
    }
}

$status_info = getEventStatusInfo($current_event['status']);
?>

<div class="events-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-calendar"></i>
                Chi Tiết Sự Kiện
            </h1>
            <p class="page-description">Xem thông tin chi tiết sự kiện #<?= $event_id ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=events&action=edit&id=<?= $event_id ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger delete-btn" 
                    data-id="<?= $event_id ?>" data-name="<?= htmlspecialchars($current_event['title']) ?>">
                <i class="fas fa-trash"></i>
                Xóa
            </button>
            <a href="?page=admin&module=events" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Event Overview -->
    <div class="event-overview">
        <div class="event-overview-grid">
            <!-- Event Image Section -->
            <div class="event-image-section">
                <div class="event-image-main" onclick="openImageZoom('<?= $current_event['image'] ?>')">
                    <img src="<?= $current_event['image'] ?>" alt="<?= htmlspecialchars($current_event['title']) ?>" 
                         onerror="this.src='<?php echo asset_url('images/placeholder.jpg'); ?>'"">
                </div>
                <div class="event-image-info">
                    <i class="fas fa-info-circle"></i>
                    Click để phóng to hình ảnh
                </div>
            </div>

            <!-- Event Info Section -->
            <div class="event-info-section">
                <div class="event-header">
                    <h2 class="event-name"><?= htmlspecialchars($current_event['title']) ?></h2>
                    <div class="event-actions">
                        <span class="status-badge status-<?= $status_info['class'] ?>">
                            <i class="fas fa-<?= $status_info['icon'] ?>"></i>
                            <?= $status_info['text'] ?>
                        </span>
                    </div>
                </div>

                <div class="event-meta">
                    <div class="meta-item">
                        <span class="meta-label">ID:</span>
                        <span class="meta-value">#<?= $current_event['id'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Slug:</span>
                        <span class="meta-value">
                            <code><?= htmlspecialchars($current_event['slug']) ?></code>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Thời gian:</span>
                        <span class="meta-value">
                            <i class="fas fa-clock"></i>
                            <?= getEventDuration($current_event['start_date'], $current_event['end_date']) ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Ngày tạo:</span>
                        <span class="meta-value">
                            <i class="fas fa-calendar"></i>
                            <?= formatDate($current_event['created_at']) ?>
                        </span>
                    </div>
                </div>

                <!-- Event Schedule -->
                <div class="event-schedule">
                    <h4>Lịch trình sự kiện</h4>
                    <div class="schedule-item">
                        <div class="schedule-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="schedule-content">
                            <div class="schedule-time"><?= formatDate($current_event['start_date']) ?></div>
                            <div class="schedule-label">Bắt đầu</div>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-icon">
                            <i class="fas fa-stop"></i>
                        </div>
                        <div class="schedule-content">
                            <div class="schedule-time"><?= formatDate($current_event['end_date']) ?></div>
                            <div class="schedule-label">Kết thúc</div>
                        </div>
                    </div>
                    <div class="schedule-item">
                        <div class="schedule-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="schedule-content">
                            <div class="schedule-time"><?= htmlspecialchars($current_event['location']) ?></div>
                            <div class="schedule-label">Địa điểm</div>
                        </div>
                    </div>
                </div>

                <!-- Event Stats -->
                <div class="event-stats-section">
                    <h4>Thống kê sự kiện</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= $current_event['current_participants'] ?></div>
                                <div class="stat-label">Đã đăng ký</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= $current_event['max_participants'] - $current_event['current_participants'] ?></div>
                                <div class="stat-label">Còn lại</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= round(($current_event['current_participants'] / $current_event['max_participants']) * 100) ?>%</div>
                                <div class="stat-label">Tỷ lệ lấp đầy</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?= formatPrice($current_event['price'] * $current_event['current_participants']) ?></div>
                                <div class="stat-label">Doanh thu</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Tabs -->
    <div class="event-details-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="showTab('details')">
                <i class="fas fa-info-circle"></i>
                Chi tiết
            </button>
            <button class="tab-btn" onclick="showTab('participants')">
                <i class="fas fa-users"></i>
                Người tham gia
            </button>
            <button class="tab-btn" onclick="showTab('analytics')">
                <i class="fas fa-chart-line"></i>
                Phân tích
            </button>
            <button class="tab-btn" onclick="showTab('history')">
                <i class="fas fa-history"></i>
                Lịch sử
            </button>
        </div>

        <div class="tabs-content">
            <!-- Details Tab -->
            <div id="details-tab" class="tab-content active">
                <div class="details-grid">
                    <div class="details-section">
                        <h4>Thông tin chi tiết</h4>
                        <table class="details-table">
                            <tr>
                                <td>Tên sự kiện:</td>
                                <td><?= htmlspecialchars($current_event['title']) ?></td>
                            </tr>
                            <tr>
                                <td>Mô tả:</td>
                                <td><?= nl2br(htmlspecialchars($current_event['description'])) ?></td>
                            </tr>
                            <tr>
                                <td>Giá vé:</td>
                                <td>
                                    <?php if ($current_event['price'] > 0): ?>
                                        <strong class="price"><?= formatPrice($current_event['price']) ?></strong>
                                    <?php else: ?>
                                        <span class="free-event">Miễn phí</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Số lượng tối đa:</td>
                                <td><?= number_format($current_event['max_participants']) ?> người</td>
                            </tr>
                            <tr>
                                <td>Đơn vị tổ chức:</td>
                                <td>ThuongLo</td>
                            </tr>
                        </table>
                    </div>

                    <div class="details-section">
                        <h4>Tiến độ đăng ký</h4>
                        <div class="progress-section">
                            <div class="progress-info">
                                <span><?= $current_event['current_participants'] ?> / <?= $current_event['max_participants'] ?> người</span>
                                <span><?= round(($current_event['current_participants'] / $current_event['max_participants']) * 100) ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?= ($current_event['current_participants'] / $current_event['max_participants']) * 100 ?>%"></div>
                            </div>
                        </div>

                        <div class="registration-stats">
                            <div class="reg-stat">
                                <span class="reg-label">Đăng ký hôm nay:</span>
                                <span class="reg-value"><?= $today_registrations ?? 0 ?></span>
                            </div>
                            <div class="reg-stat">
                                <span class="reg-label">Đăng ký tuần này:</span>
                                <span class="reg-value"><?= $week_registrations ?? 0 ?></span>
                            </div>
                            <div class="reg-stat">
                                <span class="reg-label">Tỷ lệ hủy:</span>
                                <span class="reg-value"><?= $cancellation_rate ?? 0 ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participants Tab -->
            <div id="participants-tab" class="tab-content">
                <div class="participants-section">
                    <div class="participants-header">
                        <h4>Danh sách người tham gia</h4>
                        <div class="participants-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-download"></i>
                                Xuất Excel
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-envelope"></i>
                                Gửi email
                            </button>
                        </div>
                    </div>
                    
                    <div class="participants-table-container">
                        <table class="participants-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Điện thoại</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i = 1; $i <= min(10, $current_event['current_participants']); $i++): ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>Người tham gia <?= $i ?></td>
                                        <td>user<?= $i ?>@example.com</td>
                                        <td>090123456<?= $i ?></td>
                                        <td><?= date('d/m/Y', strtotime('-5 days')) ?></td>
                                        <td>
                                            <span class="status-badge status-confirmed">Đã xác nhận</span>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                                
                                <?php if ($current_event['current_participants'] == 0): ?>
                                    <tr>
                                        <td colspan="6" class="no-data">
                                            <i class="fas fa-users"></i>
                                            <p>Chưa có người đăng ký</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics-tab" class="tab-content">
                <div class="analytics-section">
                    <h4>Phân tích sự kiện</h4>
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <div class="analytics-header">
                                <h5>Đăng ký theo ngày</h5>
                                <span class="analytics-period">30 ngày qua</span>
                            </div>
                            <div class="analytics-chart">
                                <canvas id="registrationChart" width="300" height="150"></canvas>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <div class="analytics-header">
                                <h5>Nguồn đăng ký</h5>
                            </div>
                            <div class="source-list">
                                <div class="source-item">
                                    <span class="source-name">Website</span>
                                    <span class="source-percent">60%</span>
                                </div>
                                <div class="source-item">
                                    <span class="source-name">Facebook</span>
                                    <span class="source-percent">25%</span>
                                </div>
                                <div class="source-item">
                                    <span class="source-name">Email Marketing</span>
                                    <span class="source-percent">15%</span>
                                </div>
                            </div>
                        </div>

                        <div class="analytics-card">
                            <div class="analytics-header">
                                <h5>Độ tuổi tham gia</h5>
                            </div>
                            <div class="age-distribution">
                                <div class="age-group">
                                    <span class="age-label">18-25</span>
                                    <div class="age-bar">
                                        <div class="age-progress" style="width: 30%"></div>
                                    </div>
                                    <span class="age-percent">30%</span>
                                </div>
                                <div class="age-group">
                                    <span class="age-label">26-35</span>
                                    <div class="age-bar">
                                        <div class="age-progress" style="width: 45%"></div>
                                    </div>
                                    <span class="age-percent">45%</span>
                                </div>
                                <div class="age-group">
                                    <span class="age-label">36-45</span>
                                    <div class="age-bar">
                                        <div class="age-progress" style="width: 20%"></div>
                                    </div>
                                    <span class="age-percent">20%</span>
                                </div>
                                <div class="age-group">
                                    <span class="age-label">45+</span>
                                    <div class="age-bar">
                                        <div class="age-progress" style="width: 5%"></div>
                                    </div>
                                    <span class="age-percent">5%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div id="history-tab" class="tab-content">
                <div class="history-section">
                    <h4>Lịch sử sự kiện</h4>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Tạo sự kiện</strong>
                                    <span class="timeline-date"><?= formatDate($current_event['created_at']) ?></span>
                                </div>
                                <p>Sự kiện được tạo và lên kế hoạch</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Mở đăng ký</strong>
                                    <span class="timeline-date"><?= formatDate($current_event['created_at']) ?></span>
                                </div>
                                <p>Bắt đầu nhận đăng ký từ người tham gia</p>
                            </div>
                        </div>
                        
                        <?php if ($current_event['status'] == 'ongoing'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker active"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Đang diễn ra</strong>
                                    <span class="timeline-date"><?= formatDate($current_event['start_date']) ?></span>
                                </div>
                                <p>Sự kiện đang được tổ chức</p>
                            </div>
                        </div>
                        <?php elseif ($current_event['status'] == 'completed'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Hoàn thành</strong>
                                    <span class="timeline-date"><?= formatDate($current_event['end_date']) ?></span>
                                </div>
                                <p>Sự kiện đã kết thúc thành công</p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>Xem chi tiết</strong>
                                    <span class="timeline-date"><?= date('d/m/Y H:i') ?></span>
                                </div>
                                <p>Đang xem chi tiết sự kiện</p>
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
                <h3>Xác nhận xóa</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa sự kiện <strong id="deleteEventName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <a href="?page=admin&module=events&action=delete&id=<?= $event_id ?>" class="btn btn-danger" id="confirmDelete">Xóa</a>
            </div>
        </div>
    </div>

    <!-- Image Zoom Overlay -->
    <div id="imageZoomOverlay" class="image-zoom-overlay" onclick="closeImageZoom()">
        <div class="image-zoom-container">
            <img id="zoomedImage" src="" alt="Zoomed Image">
            <button class="zoom-close" onclick="closeImageZoom()">&times;</button>
        </div>
    </div>
</div>

