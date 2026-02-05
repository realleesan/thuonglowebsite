<?php
// Load fake data
$fake_data = json_decode(file_get_contents(__DIR__ . '/../data/fake_data.json'), true);
$news = $fake_data['news'];

// Get news ID
$news_id = (int)($_GET['id'] ?? 0);

// Find news by ID
$current_news = null;
foreach ($news as $article) {
    if ($article['id'] == $news_id) {
        $current_news = $article;
        break;
    }
}

// Redirect if news not found
if (!$current_news) {
    header('Location: ?page=admin&module=news');
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
    
    if ($confirm_title !== $current_news['title']) {
        $errors[] = 'Tiêu đề xác nhận không chính xác';
    }
    
    if (empty($errors)) {
        $success = true;
        // Trong thực tế sẽ xóa khỏi database
        // Redirect sau khi xóa thành công
        if ($success) {
            header('Location: ?page=admin&module=news&deleted=1');
            exit;
        }
    }
}

// Format date function
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Get word count
function getWordCount($text) {
    return str_word_count(strip_tags($text));
}
?>

<div class="news-delete-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-trash"></i>
                Xóa Tin Tức
            </h1>
            <p class="page-description">Xác nhận xóa tin tức #<?= $news_id ?></p>
        </div>
        <div class="page-header-right">
            <a href="?page=admin&module=news&action=view&id=<?= $news_id ?>" class="btn btn-info">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="?page=admin&module=news" class="btn btn-outline">
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

    <!-- News Info Container -->
    <div class="news-info-container">
        <h3>Thông tin tin tức sẽ bị xóa</h3>
        <div class="news-info-grid">
            <div class="info-item">
                <label>ID:</label>
                <span class="info-value">#<?= $current_news['id'] ?></span>
            </div>
            <div class="info-item">
                <label>Tiêu đề:</label>
                <span class="info-value"><?= htmlspecialchars($current_news['title']) ?></span>
            </div>
            <div class="info-item">
                <label>Slug:</label>
                <span class="info-value">
                    <code><?= htmlspecialchars($current_news['slug']) ?></code>
                </span>
            </div>
            <div class="info-item">
                <label>Tác giả:</label>
                <span class="info-value"><?= htmlspecialchars($current_news['author']) ?></span>
            </div>
            <div class="info-item">
                <label>Trạng thái:</label>
                <span class="info-value">
                    <span class="status-badge status-<?= $current_news['status'] ?>">
                        <?php
                        switch($current_news['status']) {
                            case 'published': echo 'Đã xuất bản'; break;
                            case 'draft': echo 'Bản nháp'; break;
                            case 'archived': echo 'Lưu trữ'; break;
                            default: echo ucfirst($current_news['status']);
                        }
                        ?>
                    </span>
                </span>
            </div>
            <div class="info-item">
                <label>Ngày tạo:</label>
                <span class="info-value"><?= formatDate($current_news['created_at']) ?></span>
            </div>
            <div class="info-item">
                <label>Số từ:</label>
                <span class="info-value"><?= getWordCount($current_news['content']) ?> từ</span>
            </div>
            <div class="info-item">
                <label>Lượt xem:</label>
                <span class="info-value"><?= rand(100, 5000) ?> lượt</span>
            </div>
        </div>

        <div class="description-preview">
            <label>Tóm tắt:</label>
            <div class="description-content">
                <?= htmlspecialchars($current_news['excerpt']) ?>
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
                <p>Việc xóa tin tức này sẽ:</p>
                <ul>
                    <li>Xóa vĩnh viễn bài viết khỏi hệ thống</li>
                    <li>Xóa tất cả nội dung và hình ảnh liên quan</li>
                    <li>Làm mất tất cả thống kê và lượt xem</li>
                    <li>Có thể ảnh hưởng đến SEO nếu bài viết đã được index</li>
                </ul>

                <?php if ($current_news['status'] === 'published'): ?>
                    <div class="published-warning">
                        <h4>Bài viết đang được xuất bản công khai</h4>
                        <p>Bài viết này đang hiển thị trên website và có thể đã được:</p>
                        <ul>
                            <li>Người dùng đọc và chia sẻ</li>
                            <li>Google và các công cụ tìm kiếm index</li>
                            <li>Liên kết từ các trang khác</li>
                        </ul>
                        <p><strong>Khuyến nghị:</strong> Thay vì xóa, hãy chuyển sang trạng thái "Lưu trữ" để giữ lại dữ liệu.</p>
                        <div class="alternative-actions">
                            <a href="?page=admin&module=news&action=edit&id=<?= $news_id ?>" class="btn btn-warning">
                                <i class="fas fa-archive"></i>
                                Chuyển sang lưu trữ
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($current_news['status'] === 'draft'): ?>
                    <div class="safe-delete">
                        <p><i class="fas fa-info-circle"></i> Bài viết này là bản nháp nên việc xóa sẽ ít ảnh hưởng hơn.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Form Container -->
    <div class="delete-form-container">
        <form method="POST" class="delete-form">
            <h3>Xác nhận xóa tin tức</h3>
            
            <div class="confirmation-input">
                <label for="confirm_title" class="required">
                    Để xác nhận, vui lòng nhập chính xác tiêu đề tin tức:
                </label>
                <input type="text" id="confirm_title" name="confirm_title" 
                       placeholder="<?= htmlspecialchars($current_news['title']) ?>" required>
                <div class="form-note">
                    <i class="fas fa-info-circle"></i>
                    Nhập chính xác: <strong><?= htmlspecialchars($current_news['title']) ?></strong>
                </div>
            </div>

            <div class="confirmation-checkbox">
                <label>
                    <input type="checkbox" name="confirmation" value="confirmed" required>
                    Tôi hiểu rằng hành động này không thể hoàn tác và chấp nhận mọi hậu quả
                </label>
            </div>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <p><strong>Lần cuối cùng xác nhận:</strong></p>
                    <p>Bạn có chắc chắn muốn xóa vĩnh viễn tin tức "<strong><?= htmlspecialchars($current_news['title']) ?></strong>" không?</p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i>
                    Xóa Vĩnh Viễn
                </button>
                <a href="?page=admin&module=news&action=view&id=<?= $news_id ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
                <a href="?page=admin&module=news&action=edit&id=<?= $news_id ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa thay thế
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
document.querySelector('.delete-form').addEventListener('submit', function(e) {
    const confirmTitle = document.getElementById('confirm_title').value;
    const expectedTitle = <?= json_encode($current_news['title']) ?>;
    const confirmation = document.querySelector('input[name="confirmation"]:checked');
    
    if (confirmTitle !== expectedTitle) {
        e.preventDefault();
        alert('Tiêu đề xác nhận không chính xác. Vui lòng nhập chính xác tiêu đề tin tức.');
        return false;
    }
    
    if (!confirmation) {
        e.preventDefault();
        alert('Vui lòng xác nhận bằng cách tích vào checkbox.');
        return false;
    }
    
    // Final confirmation
    if (!confirm('Đây là lần xác nhận cuối cùng. Bạn có chắc chắn muốn xóa tin tức này không?')) {
        e.preventDefault();
        return false;
    }
});

// Real-time title validation
document.getElementById('confirm_title').addEventListener('input', function() {
    const expectedTitle = <?= json_encode($current_news['title']) ?>;
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (this.value === expectedTitle) {
        this.style.borderColor = '#10B981';
        this.style.backgroundColor = '#ECFDF5';
        submitBtn.disabled = false;
    } else {
        this.style.borderColor = '#EF4444';
        this.style.backgroundColor = '#FEF2F2';
        submitBtn.disabled = true;
    }
});

// Disable submit button initially
document.querySelector('button[type="submit"]').disabled = true;

// Warning before leaving page
window.addEventListener('beforeunload', function(e) {
    const confirmTitle = document.getElementById('confirm_title').value;
    if (confirmTitle.length > 0) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>