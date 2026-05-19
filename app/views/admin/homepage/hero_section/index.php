<?php
/**
 * Hero Section List View - Premium Layout
 */

// Get flash messages
$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';

// Clear flash messages
unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Quản lý Trang chủ</h1>
                    <p class="text-muted small mb-0">Chỉnh sửa nội dung Hero Section hiển thị ở đầu trang chủ.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <?php if (empty($heroSections)): ?>
                        <div class="text-center py-5">
                            <img src="https://illustrations.popsy.co/gray/taking-notes.svg" alt="Empty" style="width: 200px;" class="mb-4">
                            <h5 class="text-muted">Chưa có Hero Section nào</h5>
                            <p class="text-muted mb-4">Hero Section cần được tạo để hiển thị ở đầu trang chủ.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4" style="width: 80px;">ID</th>
                                        <th style="width: 120px;">Hình ảnh</th>
                                        <th>Nội dung Tiêu đề</th>
                                        <th class="text-center">Nút bấm</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($heroSections as $section): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted">#<?php echo $section['id']; ?></td>
                                            <td>
                                                <?php
                                                 $imgUrl = $section['image_url'] ?? '';
                                                 if ($imgUrl) {
                                                     $finalImg = (strpos($imgUrl, 'http') === 0) ? $imgUrl : img_url($imgUrl);
                                                     echo '<img src="'.$finalImg.'" class="rounded shadow-sm" style="width: 80px; height: 50px; object-fit: cover;" onerror="this.src=\'https://via.placeholder.com/80x50?text=No+Image\'">';
                                                } else {
                                                    echo '<div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width: 80px; height: 50px; font-size: 10px;">No Image</div>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1">
                                                    <?php echo mb_strimwidth(strip_tags($section['title_main']), 0, 60, '...'); ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <?php echo mb_strimwidth(strip_tags($section['subtitle']), 0, 80, '...'); ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill bg-soft-info text-info">
                                                    <?php echo $section['button_count']; ?> buttons
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($section['is_active']): ?>
                                                    <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                                        <i class="fas fa-circle me-1 small"></i> Đang hiện
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                                        <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="?page=admin&module=hero-section&action=edit&id=<?php echo $section['id']; ?>" 
                                                       class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-icon btn-light-<?php echo $section['is_active'] ? 'warning' : 'success'; ?>"
                                                            onclick="toggleStatus(<?php echo $section['id']; ?>)"
                                                            title="<?php echo $section['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                                        <i class="fas fa-<?php echo $section['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Hero Section này?')) {
        fetch('?page=admin&module=hero-section&action=toggle-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}
</script>

<style>
.bg-soft-success { background-color: #dcfce7; }
.bg-soft-info { background-color: #e0f2fe; }
.bg-soft-secondary { background-color: #f3f4f6; }
.text-success { color: #16a34a !important; }
.text-info { color: #0284c7 !important; }
.text-secondary { color: #4b5563 !important; }

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-light-primary { color: #356DF1; background-color: #eff6ff; border: none; }
.btn-light-primary:hover { background-color: #356DF1; color: white; }

.btn-light-warning { color: #d97706; background-color: #fffbeb; border: none; }
.btn-light-warning:hover { background-color: #d97706; color: white; }

.btn-light-success { color: #16a34a; background-color: #f0fdf4; border: none; }
.btn-light-success:hover { background-color: #16a34a; color: white; }

.btn-light-danger { color: #dc2626; background-color: #fef2f2; border: none; }
.btn-light-danger:hover { background-color: #dc2626; color: white; }

.table th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    color: #6b7280;
}

.card {
    border-radius: 12px;
}
</style>
