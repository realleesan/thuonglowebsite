<?php
require_once __DIR__ . '/../../_layout/admin_header.php';

// Initialize $section if not defined (for error prevention)
if (!isset($section)) {
    $section = [
        'id' => 0,
        'title' => '',
        'is_active' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Chỉnh sửa Section Sản phẩm Giá rẻ</h4>
                    <p class="text-muted mb-0">Cấu hình tiêu đề và trạng thái hiển thị section sản phẩm giá rẻ</p>
                </div>
                <a href="?page=admin&module=homepage" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="budgetProductsForm" method="POST" action="?page=admin&module=homepage&action=update-budget-products">
                        <input type="hidden" name="id" value="<?php echo $section['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-4">
                                    <label for="title" class="form-label fw-semibold">
                                        <i class="fas fa-heading text-success me-2"></i>Tiêu đề Section
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-code"></i>
                                        </span>
                                        <textarea class="form-control" id="title" name="title" rows="3" 
                                                  placeholder="Nhập tiêu đề section (có thể dùng HTML)"><?php echo htmlspecialchars($section['title']); ?></textarea>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Bạn có thể sử dụng HTML để định dạng tiêu đề. Ví dụ: 
                                            <code>&lt;h2&gt;Sản phẩm &lt;span class="highlight"&gt;Giá rẻ&lt;/span&gt;&lt;/h2&gt;</code>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-eye text-primary me-2"></i>Trạng thái hiển thị
                                    </label>
                                    <div class="form-check form-switch form-check-lg">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                               id="isActive" <?php echo $section['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isActive">
                                            <span class="badge bg-<?php echo $section['is_active'] ? 'success' : 'secondary'; ?> ms-2">
                                                <?php echo $section['is_active'] ? 'Đang hiển thị' : 'Đang ẩn'; ?>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">
                                            <i class="fas fa-toggle-on me-1"></i>
                                            Bật/tắt hiển thị section trên trang chủ
                                        </small>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar text-primary me-2"></i>Thông tin
                                    </label>
                                    <div class="small text-muted">
                                        <div class="mb-2">
                                            <strong>ID:</strong> #<?php echo $section['id']; ?>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($section['created_at'])); ?>
                                        </div>
                                        <div>
                                            <strong>Cập nhật lần cuối:</strong> <?php echo date('d/m/Y H:i', strtotime($section['updated_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-4">
                            <div class="d-flex justify-content-between">
                                <a href="?page=admin&module=homepage" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                                <div>
                                    <button type="button" class="btn btn-outline-success me-2" onclick="previewTitle()">
                                        <i class="fas fa-eye me-2"></i>Xem trước
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preview Modal -->
            <div class="modal fade" id="previewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Xem trước tiêu đề</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center p-4 bg-light rounded">
                                <div id="previewContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-lg .form-check-input {
    width: 2em;
    height: 2em;
}

.form-check-lg .form-check-label {
    font-size: 1.1rem;
    margin-left: 0.5rem;
}

.badge {
    font-size: 0.8em;
}

.preview-box {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    background: #f8f9fa;
}

.highlight {
    color: #198754;
    font-weight: bold;
}
</style>

<script>
document.getElementById('isActive').addEventListener('change', function() {
    const badge = this.nextElementSibling.querySelector('.badge');
    if (this.checked) {
        badge.className = 'badge bg-success ms-2';
        badge.textContent = 'Đang hiển thị';
    } else {
        badge.className = 'badge bg-secondary ms-2';
        badge.textContent = 'Đang ẩn';
    }
});

function previewTitle() {
    const title = document.getElementById('title').value;
    const previewContent = document.getElementById('previewContent');
    
    if (title.trim()) {
        previewContent.innerHTML = title;
    } else {
        previewContent.innerHTML = '<span class="text-muted">Không có tiêu đề để xem trước</span>';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

// Form submission with AJAX
document.getElementById('budgetProductsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...';
    
    fetch('?page=admin&module=homepage&action=update-budget-products', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message || 'Đã cập nhật section thành công!');
            
            // Redirect after a short delay
            setTimeout(() => {
                window.location.href = '?page=admin&module=homepage';
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Có lỗi xảy ra khi cập nhật section.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Có lỗi xảy ra khi kết nối đến server.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

function showAlert(type, message) {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the card body
    const cardBody = document.querySelector('.card-body');
    cardBody.insertBefore(alert, cardBody.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}
</script>

<?php
require_once __DIR__ . '/../../_layout/admin_footer.php';
?>
