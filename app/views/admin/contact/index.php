<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$contacts = $fake_data['contacts'];

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Filter contacts
$filtered_contacts = $contacts;

if (!empty($search)) {
    $filtered_contacts = array_filter($filtered_contacts, function($contact) use ($search) {
        return stripos($contact['name'], $search) !== false || 
               stripos($contact['email'], $search) !== false ||
               stripos($contact['phone'], $search) !== false ||
               stripos($contact['subject'], $search) !== false ||
               stripos($contact['message'], $search) !== false;
    });
}

if (!empty($status_filter)) {
    $filtered_contacts = array_filter($filtered_contacts, function($contact) use ($status_filter) {
        return $contact['status'] == $status_filter;
    });
}

// Pagination
$per_page = 10;
$total_contacts = count($filtered_contacts);
$total_pages = ceil($total_contacts / $per_page);
$current_page = max(1, min($total_pages, (int)($_GET['page'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$paged_contacts = array_slice($filtered_contacts, $offset, $per_page);

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<div class="contacts-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-envelope"></i>
                Quản Lý Liên Hệ
            </h1>
            <p class="page-description">Quản lý các tin nhắn liên hệ từ khách hàng</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="module" value="contact">
            
            <div class="filter-group">
                <div class="filter-item">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Tên, email, số điện thoại, chủ đề...">
                </div>
                
                <div class="filter-item">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="new" <?= $status_filter == 'new' ? 'selected' : '' ?>>Mới</option>
                        <option value="read" <?= $status_filter == 'read' ? 'selected' : '' ?>>Đã đọc</option>
                        <option value="replied" <?= $status_filter == 'replied' ? 'selected' : '' ?>>Đã trả lời</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="?page=admin&module=contact" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <span class="results-count">
            Hiển thị <?= count($paged_contacts) ?> trong tổng số <?= $total_contacts ?> liên hệ
        </span>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <select id="bulk-action" disabled>
                <option value="">Hành động hàng loạt</option>
                <option value="mark-read">Đánh dấu đã đọc</option>
                <option value="mark-replied">Đánh dấu đã trả lời</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="button" id="apply-bulk" class="btn btn-secondary" disabled>
                Áp dụng
            </button>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" id="select-all">
                    </th>
                    <th width="60">ID</th>
                    <th>Thông tin liên hệ</th>
                    <th width="200">Chủ đề</th>
                    <th width="300">Tin nhắn</th>
                    <th width="100">Trạng thái</th>
                    <th width="120">Ngày gửi</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paged_contacts)): ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy liên hệ nào</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paged_contacts as $contact): ?>
                        <tr class="<?= $contact['status'] == 'new' ? 'unread-row' : '' ?>">
                            <td>
                                <input type="checkbox" class="contact-checkbox" value="<?= $contact['id'] ?>">
                            </td>
                            <td><?= $contact['id'] ?></td>
                            <td>
                                <div class="contact-info">
                                    <h4 class="contact-name">
                                        <?= htmlspecialchars($contact['name']) ?>
                                        <?php if ($contact['status'] == 'new'): ?>
                                            <span class="new-badge">Mới</span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="contact-email">
                                        <i class="fas fa-envelope"></i>
                                        <?= htmlspecialchars($contact['email']) ?>
                                    </p>
                                    <p class="contact-phone">
                                        <i class="fas fa-phone"></i>
                                        <?= htmlspecialchars($contact['phone']) ?>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <div class="subject-cell">
                                    <?= htmlspecialchars($contact['subject']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="message-preview">
                                    <?= htmlspecialchars(mb_substr($contact['message'], 0, 100)) ?>
                                    <?php if (mb_strlen($contact['message']) > 100): ?>
                                        <span class="text-muted">...</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
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
                            </td>
                            <td><?= formatDate($contact['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=contact&action=view&id=<?= $contact['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?page=admin&module=contact&action=edit&id=<?= $contact['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Cập nhật trạng thái">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                            data-id="<?= $contact['id'] ?>" data-name="<?= htmlspecialchars($contact['name']) ?>" 
                                            title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=admin&module=contact&<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1): ?>
                    <a href="?page=admin&module=contact&<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                       class="pagination-number">1</a>
                    <?php if ($start_page > 2): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=contact&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="pagination-number <?= $i == $current_page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <span class="pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="?page=admin&module=contact&<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                       class="pagination-number"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=admin&module=contact&<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>" 
                       class="pagination-btn">
                        Sau
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                Trang <?= $current_page ?> / <?= $total_pages ?>
            </div>
        </div>
    <?php endif; ?>

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
</div>