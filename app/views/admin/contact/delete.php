<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$contacts = $fake_data['contacts'];

// Get contact ID from URL
$contact_id = (int)($_GET['id'] ?? 0);

// Find contact
$contact = null;
foreach ($contacts as $c) {
    if ($c['id'] == $contact_id) {
        $contact = $c;
        break;
    }
}

// Redirect if contact not found
if (!$contact) {
    header('Location: ?page=admin&module=contact');
    exit;
}

// Handle deletion (demo - không xóa thật)
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm === 'yes') {
        // Demo success message
        $success_message = 'Xóa liên hệ thành công!';
        // In real app, would redirect after deletion
        // header('Location: ?page=admin&module=contact&deleted=1');
        // exit;
    } else {
        $error_message = 'Vui lòng xác nhận để xóa liên hệ.';
    }
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="contact-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Liên Hệ #<?= $contact['id'] ?>
            </h1>
            <p class="page-description">Xác nhận xóa liên hệ từ khách hàng</p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=contact&action=view&id=<?= $contact['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success_message) ?>
            <div class="alert-actions">
                <a href="?page=admin&module=contact" class="btn btn-sm btn-success">
                    Về danh sách liên hệ
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!$success_message): ?>
        <div class="delete-content">
            <!-- Warning Alert -->
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Cảnh báo:</strong> Bạn đang thực hiện xóa liên hệ. Hành động này không thể hoàn tác!
            </div>

            <div class="content-grid">
                <!-- Contact Information -->
                <div class="info-section">
                    <div class="detail-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-info-circle"></i>
                                Thông Tin Liên Hệ Sẽ Bị Xóa
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <label>ID:</label>
                                <span>#<?= $contact['id'] ?></span>
                            </div>
                            <div class="info-row">
                                <label>Họ tên:</label>
                                <span><?= htmlspecialchars($contact['name']) ?></span>
                            </div>
                            <div class="info-row">
                                <label>Email:</label>
                                <span>
                                    <i class="fas fa-envelope"></i>
                                    <?= htmlspecialchars($contact['email']) ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <label>Số điện thoại:</label>
                                <span>
                                    <i class="fas fa-phone"></i>
                                    <?= htmlspecialchars($contact['phone']) ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <label>Chủ đề:</label>
                                <span><?= htmlspecialchars($contact['subject']) ?></span>
                            </div>
                            <div class="info-row">
                                <label>Trạng thái:</label>
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
                            <div class="info-row">
                                <label>Ngày gửi:</label>
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    <?= formatDate($contact['created_at']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Preview -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-comment"></i>
                                Nội Dung Tin Nhắn
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="message-preview">
                                <?= nl2br(htmlspecialchars($contact['message'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <div class="form-section">
                    <div class="detail-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-exclamation-triangle"></i>
                                Xác Nhận Xóa
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="delete-form">
                                <div class="confirmation-section">
                                    <div class="warning-box">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <h4>Hậu quả khi xóa liên hệ:</h4>
                                        <ul>
                                            <li>Tất cả thông tin liên hệ sẽ bị xóa vĩnh viễn</li>
                                            <li>Không thể khôi phục lại dữ liệu</li>
                                            <li>Lịch sử xử lý sẽ bị mất</li>
                                            <li>Không thể trả lời khách hàng sau khi xóa</li>
                                        </ul>
                                    </div>

                                    <div class="form-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="confirm" value="yes" required>
                                            <span class="checkmark"></span>
                                            Tôi hiểu rằng hành động này không thể hoàn tác và đồng ý xóa liên hệ này
                                        </label>
                                    </div>

                                    <div class="form-group">
                                        <label for="delete_reason" class="form-label">
                                            Lý do xóa (tùy chọn):
                                        </label>
                                        <select id="delete_reason" name="delete_reason" class="form-control">
                                            <option value="">-- Chọn lý do --</option>
                                            <option value="spam">Tin nhắn spam</option>
                                            <option value="duplicate">Trùng lặp</option>
                                            <option value="resolved">Đã xử lý xong</option>
                                            <option value="invalid">Thông tin không hợp lệ</option>
                                            <option value="other">Lý do khác</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                        Xác nhận xóa
                                    </button>
                                    <a href="?page=admin&module=contact&action=view&id=<?= $contact['id'] ?>" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                        Hủy bỏ
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Alternative Actions -->
                    <div class="detail-card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-lightbulb"></i>
                                Thay Vì Xóa
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Thay vì xóa, bạn có thể:</p>
                            <div class="alternative-actions">
                                <a href="?page=admin&module=contact&action=edit&id=<?= $contact['id'] ?>" 
                                   class="btn btn-info btn-block">
                                    <i class="fas fa-edit"></i>
                                    Cập nhật trạng thái thành "Đã xử lý"
                                </a>
                                
                                <button type="button" class="btn btn-success btn-block reply-btn" 
                                        data-email="<?= htmlspecialchars($contact['email']) ?>" 
                                        data-subject="Re: <?= htmlspecialchars($contact['subject']) ?>">
                                    <i class="fas fa-reply"></i>
                                    Trả lời khách hàng trước
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

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