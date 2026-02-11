<?php
// Load ViewDataService
require_once __DIR__ . '/../../../services/ViewDataService.php';
require_once __DIR__ . '/../../../services/ErrorHandler.php';

try {
    $viewDataService = new ViewDataService();
    
    // Get contact ID from URL
    $contact_id = (int)($_GET['id'] ?? 0);
    
    // Get contact details using ViewDataService
    $contactData = $viewDataService->getAdminContactDetailsData($contact_id);
    $contact = $contactData['contact'];
    
    // Redirect if contact not found
    if (!$contact) {
        header('Location: ?page=admin&module=contact&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    ErrorHandler::logError('Admin Contact View Error', $e);
    header('Location: ?page=admin&module=contact&error=system_error');
    exit;
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="contact-view-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-envelope"></i>
                Chi Tiết Liên Hệ #<?= $contact['id'] ?>
            </h1>
            <p class="page-description">Xem thông tin chi tiết liên hệ từ khách hàng</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=contact" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <a href="?page=admin&module=contact&action=edit&id=<?= $contact['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Cập nhật trạng thái
            </a>
        </div>
    </div>

    <!-- Contact Details -->
    <div class="contact-details">
        <div class="details-grid">
            <!-- Contact Information Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-user"></i>
                        Thông Tin Liên Hệ
                    </h3>
                    <span class="status-badge status-<?= $contact['status'] ?>">
                        <?php
                        switch($contact['status']) {
                            case 'new': echo 'Mới'; break;
                            case 'read': echo 'Đã đọc'; break;
                            case 'replied': echo 'Đã trả lời'; break;
                            default: echo 'N/A';
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <label>Họ tên:</label>
                        <span><?= htmlspecialchars($contact['name']) ?></span>
                    </div>
                    <div class="info-row">
                        <label>Email:</label>
                        <span>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                <?= htmlspecialchars($contact['email']) ?>
                            </a>
                        </span>
                    </div>
                    <div class="info-row">
                        <label>Số điện thoại:</label>
                        <span>
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?= htmlspecialchars($contact['phone']) ?>">
                                <?= htmlspecialchars($contact['phone']) ?>
                            </a>
                        </span>
                    </div>
                    <div class="info-row">
                        <label>Ngày gửi:</label>
                        <span>
                            <i class="fas fa-calendar"></i>
                            <?= formatDate($contact['created_at']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Message Content Card -->
            <div class="detail-card message-card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-comment"></i>
                        Nội Dung Tin Nhắn
                    </h3>
                </div>
                <div class="card-body">
                    <div class="subject-section">
                        <label>Chủ đề:</label>
                        <h4 class="subject-title"><?= htmlspecialchars($contact['subject']) ?></h4>
                    </div>
                    <div class="message-section">
                        <label>Tin nhắn:</label>
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($contact['message'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-section">
            <div class="action-buttons">
                <?php if ($contact['status'] == 'new'): ?>
                    <button type="button" class="btn btn-info mark-read-btn" data-id="<?= $contact['id'] ?>">
                        <i class="fas fa-eye"></i>
                        Đánh dấu đã đọc
                    </button>
                <?php endif; ?>
                
                <?php if ($contact['status'] != 'replied'): ?>
                    <button type="button" class="btn btn-success reply-btn" data-email="<?= htmlspecialchars($contact['email']) ?>" 
                            data-subject="Re: <?= htmlspecialchars($contact['subject']) ?>">
                        <i class="fas fa-reply"></i>
                        Trả lời email
                    </button>
                <?php endif; ?>
                
                <button type="button" class="btn btn-danger delete-btn" 
                        data-id="<?= $contact['id'] ?>" data-name="<?= htmlspecialchars($contact['name']) ?>">
                    <i class="fas fa-trash"></i>
                    Xóa liên hệ
                </button>
            </div>
        </div>

        <!-- Timeline Section (if needed for future features) -->
        <div class="timeline-section">
            <div class="detail-card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-history"></i>
                        Lịch Sử Xử Lý
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Liên hệ được gửi</h4>
                                <p class="timeline-date"><?= formatDate($contact['created_at']) ?></p>
                                <p>Khách hàng <?= htmlspecialchars($contact['name']) ?> đã gửi liên hệ với chủ đề "<?= htmlspecialchars($contact['subject']) ?>"</p>
                            </div>
                        </div>
                        
                        <?php if ($contact['status'] != 'new'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Đã xem</h4>
                                    <p class="timeline-date">Đã được xem bởi admin</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($contact['status'] == 'replied'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <i class="fas fa-reply"></i>
                                </div>
                                <div class="timeline-content">
                                    <h4>Đã trả lời</h4>
                                    <p class="timeline-date">Admin đã trả lời liên hệ</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
                <p>Bạn có chắc chắn muốn xóa liên hệ từ <strong id="deleteContactName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDelete">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Trả lời email</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bạn sẽ được chuyển đến ứng dụng email mặc định để trả lời.</p>
                <div class="reply-info">
                    <p><strong>Gửi đến:</strong> <span id="replyEmail"></span></p>
                    <p><strong>Chủ đề:</strong> <span id="replySubject"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelReply">Hủy</button>
                <button type="button" class="btn btn-success" id="confirmReply">Mở email</button>
            </div>
        </div>
    </div>
</div>