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

// Handle form submission (demo - không lưu thật)
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_status = $_POST['status'] ?? '';
    $admin_notes = $_POST['admin_notes'] ?? '';
    
    // Validate
    if (empty($new_status)) {
        $error_message = 'Vui lòng chọn trạng thái.';
    } else {
        // Demo success message
        $success_message = 'Cập nhật trạng thái liên hệ thành công!';
        $contact['status'] = $new_status; // Update for display
    }
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="contact-edit-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-edit"></i>
                Cập Nhật Trạng Thái Liên Hệ #<?= $contact['id'] ?>
            </h1>
            <p class="page-description">Cập nhật trạng thái xử lý liên hệ từ khách hàng</p>
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
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <div class="edit-content">
        <div class="content-grid">
            <!-- Contact Information (Read-only) -->
            <div class="info-section">
                <div class="detail-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-info-circle"></i>
                            Thông Tin Liên Hệ
                        </h3>
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
                            <label>Ngày gửi:</label>
                            <span>
                                <i class="fas fa-calendar"></i>
                                <?= formatDate($contact['created_at']) ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <label>Trạng thái hiện tại:</label>
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
                    </div>
                </div>

                <!-- Message Content -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-comment"></i>
                            Nội Dung Tin Nhắn
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($contact['message'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="form-section">
                <div class="detail-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-edit"></i>
                            Cập Nhật Trạng Thái
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="edit-form">
                            <div class="form-group">
                                <label for="status" class="form-label required">
                                    Trạng thái mới:
                                </label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="">-- Chọn trạng thái --</option>
                                    <option value="new" <?= $contact['status'] == 'new' ? 'selected' : '' ?>>
                                        Mới
                                    </option>
                                    <option value="read" <?= $contact['status'] == 'read' ? 'selected' : '' ?>>
                                        Đã đọc
                                    </option>
                                    <option value="replied" <?= $contact['status'] == 'replied' ? 'selected' : '' ?>>
                                        Đã trả lời
                                    </option>
                                </select>
                                <small class="form-help">
                                    Chọn trạng thái phù hợp với tình trạng xử lý hiện tại
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="admin_notes" class="form-label">
                                    Ghi chú của admin:
                                </label>
                                <textarea id="admin_notes" name="admin_notes" class="form-control" 
                                          rows="4" placeholder="Nhập ghi chú về việc xử lý liên hệ này (tùy chọn)"></textarea>
                                <small class="form-help">
                                    Ghi chú nội bộ để theo dõi quá trình xử lý
                                </small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Cập nhật trạng thái
                                </button>
                                <a href="?page=admin&module=contact&action=view&id=<?= $contact['id'] ?>" 
                                   class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="detail-card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-bolt"></i>
                            Thao Tác Nhanh
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <button type="button" class="btn btn-info btn-block reply-btn" 
                                    data-email="<?= htmlspecialchars($contact['email']) ?>" 
                                    data-subject="Re: <?= htmlspecialchars($contact['subject']) ?>">
                                <i class="fas fa-reply"></i>
                                Trả lời email ngay
                            </button>
                            
                            <button type="button" class="btn btn-success btn-block mark-replied-btn" 
                                    data-id="<?= $contact['id'] ?>">
                                <i class="fas fa-check"></i>
                                Đánh dấu đã trả lời
                            </button>
                            
                            <button type="button" class="btn btn-warning btn-block call-btn" 
                                    data-phone="<?= htmlspecialchars($contact['phone']) ?>">
                                <i class="fas fa-phone"></i>
                                Gọi điện thoại
                            </button>
                        </div>
                    </div>
                </div>
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