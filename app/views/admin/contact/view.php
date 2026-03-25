<?php
/**
 * Admin Contact View
 * Sử dụng AdminService thông qua ServiceManager
 */

// Khởi tạo View & ServiceManager
require_once __DIR__ . '/../../../../core/view_init.php';

// Chọn service admin (được inject từ index.php)
$service = isset($currentService) ? $currentService : ($adminService ?? null);

try {
    // Get contact ID from URL
    $contact_id = (int)($_GET['id'] ?? 0);
    
    // Get contact details using AdminService
    $contactData = $service->getContactDetailsData($contact_id);
    $contact = $contactData['contact'];
    
    // Redirect if contact not found
    if (!$contact) {
        header('Location: ?page=admin&module=contact&error=not_found');
        exit;
    }
    
} catch (Exception $e) {
    $errorHandler->logError('Admin Contact View Error', $e);
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
                <button type="button" class="btn btn-danger delete-btn" 
                        data-id="<?= $contact['id'] ?>" data-name="<?= htmlspecialchars($contact['name']) ?>">
                    <i class="fas fa-trash"></i>
                    Xóa liên hệ
                </button>
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
    </style>

    <script>
    // Delete button click handler
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name || 'danh mục này';
                showProductDeleteModal(id, name);
            });
        });
    });

    window.showProductDeleteModal = function(id, name) {
        const modal = document.getElementById('productDeleteModal');
        const nameElement = document.getElementById('productDeleteName');
    
        if (modal) {
            if (nameElement) {
                nameElement.textContent = name || 'danh mục này';
            }
            modal.style.display = 'block';
            modal.dataset.deleteId = id;
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeProductDeleteModal = function() {
        const modal = document.getElementById('productDeleteModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            delete modal.dataset.deleteId;
        }
    };

    // Handle confirm delete - AJAX
    document.addEventListener('click', function(e) {
        if (e.target.id === 'prConfirmDeleteBtn') {
            const modal = document.getElementById('productDeleteModal');
            const deleteId = modal ? modal.dataset.deleteId : null;
            if (deleteId) {
                // AJAX delete
                fetch('?page=admin&module=contact&action=delete&id=' + deleteId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeProductDeleteModal();
                        // Reload page to show updated list
                        window.location.href = '?page=admin&module=contact';
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi xóa liên hệ');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa liên hệ');
                });
            }
        }
    });

    // Close on overlay click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('product-modal-overlay')) {
            closeProductDeleteModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('productDeleteModal');
            if (modal && modal.style.display === 'block') {
                closeProductDeleteModal();
            }
        }
    });
    </script>

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