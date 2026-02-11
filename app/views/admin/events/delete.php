<?php
// Load ViewDataService
require_once __DIR__ . '/../../services/ViewDataService.php';
require_once __DIR__ . '/../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    
    // Get event ID
    $event_id = (int)($_GET['id'] ?? 0);
    
    if (!$event_id) {
        header('Location: ?page=admin&module=events');
        exit;
    }
    
    // Get event data from service
    $eventData = $viewDataService->getAdminEventDetailsData($event_id);
    $current_event = $eventData['event'];
    
    // Redirect if event not found
    if (!$current_event) {
        header('Location: ?page=admin&module=events');
        exit;
    }
    
} catch (Exception $e) {
    ErrorHandler::logError('Admin Events Delete Error', $e);
    header('Location: ?page=admin&module=events&error=1');
    exit;
}

// Handle form submission (demo - không xóa thật)
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmation = $_POST['confirmation'] ?? '';
    $confirm_title = $_POST['confirm_title'] ?? '';
    
    // Validation
    if (empty($confirmation)) {
        $errors[] = 'Vui lòng xác nhận việc xóa';
    }
    
    if ($confirm_title !== $current_event['title']) {
        $errors[] = 'Tên sự kiện xác nhận không chính xác';
    }
    
    if (empty($errors)) {
        // Delete from database
        if ($eventsModel->delete($event_id)) {
            $success = true;
            header('Location: ?page=admin&module=events&deleted=1');
            exit;
        } else {
            $errors[] = 'Có lỗi xảy ra khi xóa sự kiện';
        }
    }
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Get event status info
function getEventStatusInfo($status) {
    switch($status) {
        case 'upcoming': return ['text' => 'Sắp diễn ra', 'class' => 'upcoming'];
        case 'ongoing': return ['text' => 'Đang diễn ra', 'class' => 'ongoing'];
        case 'completed': return ['text' => 'Đã kết thúc', 'class' => 'completed'];
        case 'cancelled': return ['text' => 'Đã hủy', 'class' => 'cancelled'];
        default: return ['text' => ucfirst($status), 'class' => $status];
    }
}

$status_info = getEventStatusInfo($current_event['status']);
?>

<div class="events-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Sự Kiện
            </h1>
            <p class="page-description">Xác nhận xóa sự kiện #<?= $event_id ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=events&action=view&id=<?= $event_id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=events" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Event Info Container -->
    <div class="event-info-container">
        <h3>Thông tin sự kiện sẽ bị xóa</h3>
        <div class="event-info-grid">
            <div class="info-item">
                <label>ID:</label>
                <span class="info-value">#<?= $current_event['id'] ?></span>
            </div>
            <div class="info-item">
                <label>Tên sự kiện:</label>
                <span class="info-value"><?= htmlspecialchars($current_event['title']) ?></span>
            </div>
            <div class="info-item">
                <label>Slug:</label>
                <span class="info-value">
                    <code><?= htmlspecialchars($current_event['slug']) ?></code>
                </span>
            </div>
            <div class="info-item">
                <label>Thời gian:</label>
                <span class="info-value">
                    <?= formatDate($current_event['start_date']) ?> - <?= formatDate($current_event['end_date']) ?>
                </span>
            </div>
            <div class="info-item">
                <label>Địa điểm:</label>
                <span class="info-value"><?= htmlspecialchars($current_event['location']) ?></span>
            </div>
            <div class="info-item">
                <label>Trạng thái:</label>
                <span class="info-value">
                    <span class="status-badge status-<?= $status_info['class'] ?>">
                        <?= $status_info['text'] ?>
                    </span>
                </span>
            </div>
            <div class="info-item">
                <label>Giá vé:</label>
                <span class="info-value">
                    <?php if ($current_event['price'] > 0): ?>
                        <?= formatPrice($current_event['price']) ?>
                    <?php else: ?>
                        <span class="free-event">Miễn phí</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-item">
                <label>Người tham gia:</label>
                <span class="info-value"><?= $current_event['current_participants'] ?> / <?= $current_event['max_participants'] ?></span>
            </div>
            <div class="info-item">
                <label>Ngày tạo:</label>
                <span class="info-value"><?= formatDate($current_event['created_at']) ?></span>
            </div>
            <div class="info-item">
                <label>Doanh thu:</label>
                <span class="info-value"><?= formatPrice($current_event['price'] * $current_event['current_participants']) ?></span>
            </div>
        </div>

        <div class="description-preview">
            <label>Mô tả sự kiện:</label>
            <div class="description-content">
                <?= nl2br(htmlspecialchars($current_event['description'])) ?>
            </div>
        </div>
    </div>

    <!-- Warning Section -->
    <div class="delete-warning-container">
        <div class="delete-warning">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="warning-content">
                <h3>Cảnh báo: Hành động không thể hoàn tác!</h3>
                <p>Việc xóa sự kiện này sẽ:</p>
                <ul>
                    <li>Xóa vĩnh viễn sự kiện khỏi hệ thống</li>
                    <li>Xóa tất cả thông tin đăng ký của người tham gia</li>
                    <li>Làm mất tất cả thống kê và dữ liệu phân tích</li>
                    <li>Ảnh hưởng đến những người đã đăng ký tham gia</li>
                    <li>Có thể ảnh hưởng đến uy tín nếu sự kiện đã được quảng bá</li>
                </ul>

                <?php if ($current_event['current_participants'] > 0): ?>
                    <div class="participants-warning">
                        <h4>Có <?= $current_event['current_participants'] ?> người đã đăng ký tham gia</h4>
                        <p>Sự kiện này đã có người đăng ký. Việc xóa sẽ:</p>
                        <ul>
                            <li>Làm mất thông tin đăng ký của <?= $current_event['current_participants'] ?> người</li>
                            <li>Gây khó chịu cho những người đã đăng ký</li>
                            <li>Có thể cần thông báo hủy sự kiện trước</li>
                        </ul>
                        <p><strong>Khuyến nghị:</strong> Thay vì xóa, hãy chuyển trạng thái thành "Đã hủy" và gửi thông báo cho người tham gia.</p>
                        <div class="alternative-actions">
                            <a href="?page=admin&module=events&action=edit&id=<?= $event_id ?>" class="btn btn-warning">
                                <i class="fas fa-ban"></i>
                                Hủy sự kiện thay vì xóa
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($current_event['status'] === 'ongoing'): ?>
                    <div class="ongoing-warning">
                        <h4>Sự kiện đang diễn ra</h4>
                        <p><strong>Cảnh báo nghiêm trọng:</strong> Sự kiện này đang diễn ra. Việc xóa có thể gây ra:</p>
                        <ul>
                            <li>Gián đoạn nghiêm trọng cho người tham gia</li>
                            <li>Mất mát dữ liệu quan trọng đang được thu thập</li>
                            <li>Ảnh hưởng xấu đến uy tín tổ chức</li>
                        </ul>
                        <p><strong>Không khuyến nghị xóa sự kiện đang diễn ra!</strong></p>
                    </div>
                <?php endif; ?>

                <?php if ($current_event['status'] === 'upcoming' && $current_event['current_participants'] == 0): ?>
                    <div class="safe-delete">
                        <p><i class="fas fa-info-circle"></i> Sự kiện này chưa có người đăng ký nên việc xóa sẽ ít ảnh hưởng hơn.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Form Container -->
    <div class="delete-form-container">
        <form method="POST" class="delete-form">
            <h3>Xác nhận xóa sự kiện</h3>
            
            <div class="confirmation-input">
                <label for="confirm_title" class="required">
                    Để xác nhận, vui lòng nhập chính xác tên sự kiện:
                </label>
                <input type="text" id="confirm_title" name="confirm_title" 
                       placeholder="<?= htmlspecialchars($current_event['title']) ?>" required>
                <div class="form-note">
                    <i class="fas fa-info-circle"></i>
                    Nhập chính xác: <strong><?= htmlspecialchars($current_event['title']) ?></strong>
                </div>
            </div>

            <div class="confirmation-checkbox">
                <label>
                    <input type="checkbox" name="confirmation" value="confirmed" required>
                    Tôi hiểu rằng hành động này không thể hoàn tác và chấp nhận mọi hậu quả
                </label>
            </div>

            <?php if ($current_event['current_participants'] > 0): ?>
                <div class="participants-confirmation">
                    <label>
                        <input type="checkbox" name="participants_confirmation" value="confirmed" required>
                        Tôi hiểu rằng việc xóa sẽ ảnh hưởng đến <?= $current_event['current_participants'] ?> người đã đăng ký
                    </label>
                </div>
            <?php endif; ?>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <p><strong>Lần cuối cùng xác nhận:</strong></p>
                    <p>Bạn có chắc chắn muốn xóa vĩnh viễn sự kiện "<strong><?= htmlspecialchars($current_event['title']) ?></strong>" không?</p>
                    <?php if ($current_event['current_participants'] > 0): ?>
                        <p class="text-danger">Điều này sẽ ảnh hưởng đến <?= $current_event['current_participants'] ?> người đã đăng ký!</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Xóa Vĩnh Viễn
                </button>
                <a href="?page=admin&module=events&action=view&id=<?= $event_id ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
                <a href="?page=admin&module=events&action=edit&id=<?= $event_id ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa thay thế
                </a>
            </div>
        </form>
    </div>
</div>

