<?php
// Professional Contact Messages Management
$page_title = "Tin nhắn Liên hệ";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Liên hệ', 'url' => null]
];

// Mock contact messages
$messages = [
    [
        'id' => 1,
        'name' => 'Nguyễn Văn A',
        'email' => 'nguyenvana@example.com',
        'phone' => '0901234567',
        'subject' => 'Hỏi về khóa học Web Development',
        'message' => 'Tôi muốn biết thêm thông tin về khóa học Web Development. Khóa học có phù hợp với người mới bắt đầu không?',
        'status' => 'new',
        'created_at' => '2024-02-04 10:30:00'
    ],
    [
        'id' => 2,
        'name' => 'Trần Thị B',
        'email' => 'tranthib@example.com',
        'phone' => '0912345678',
        'subject' => 'Yêu cầu hỗ trợ kỹ thuật',
        'message' => 'Tôi gặp vấn đề khi đăng nhập vào hệ thống. Bạn có thể hỗ trợ tôi không?',
        'status' => 'replied',
        'created_at' => '2024-02-03 15:20:00'
    ],
    [
        'id' => 3,
        'name' => 'Lê Văn C',
        'email' => 'levanc@example.com',
        'phone' => '0923456789',
        'subject' => 'Đề xuất hợp tác',
        'message' => 'Công ty chúng tôi muốn hợp tác với ThuongLo trong việc đào tạo nhân sự. Vui lòng liên hệ lại.',
        'status' => 'archived',
        'created_at' => '2024-02-01 09:00:00'
    ]
];

// Get filter
$filterStatus = $_GET['status'] ?? '';

// Apply filter
$filteredMessages = $messages;
if ($filterStatus) {
    $filteredMessages = array_filter($filteredMessages, function($m) use ($filterStatus) {
        return $m['status'] === $filterStatus;
    });
}

// Stats
$stats = [
    'total' => count($messages),
    'new' => count(array_filter($messages, function($m) { return $m['status'] === 'new'; })),
    'replied' => count(array_filter($messages, function($m) { return $m['status'] === 'replied'; })),
];
?>

<div class="admin-page-header">
    <div class="page-header-left">
        <h1><?php echo $page_title; ?></h1>
        <div class="admin-breadcrumb">
            <?php foreach ($breadcrumb as $index => $crumb): ?>
                <?php if ($crumb['url']): ?>
                    <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['text']; ?></a>
                <?php else: ?>
                    <span class="current"><?php echo $crumb['text']; ?></span>
                <?php endif; ?>
                <?php if ($index < count($breadcrumb) - 1): ?>
                    <span class="delimiter">/</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Stats Summary -->
<div class="admin-stats-summary">
    <div class="stat-item">
        <span class="stat-label">Tổng tin nhắn:</span>
        <span class="stat-value"><?php echo $stats['total']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Mới:</span>
        <span class="stat-value" style="color: #F59E0B;"><?php echo $stats['new']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Đã trả lời:</span>
        <span class="stat-value text-success"><?php echo $stats['replied']; ?></span>
    </div>
</div>

<!-- Filters -->
<div class="admin-filters-bar">
    <form method="GET" action="" class="filters-form">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="module" value="contact">
        
        <div class="filter-group">
            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="new" <?php echo $filterStatus === 'new' ? 'selected' : ''; ?>>Mới</option>
                <option value="replied" <?php echo $filterStatus === 'replied' ? 'selected' : ''; ?>>Đã trả lời</option>
                <option value="archived" <?php echo $filterStatus === 'archived' ? 'selected' : ''; ?>>Đã lưu trữ</option>
            </select>
        </div>
        
        <?php if ($filterStatus): ?>
        <a href="?page=admin&module=contact" class="admin-btn admin-btn-secondary">
            <i class="fas fa-times"></i> Xóa bộ lọc
        </a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <div class="messages-list">
            <?php foreach ($filteredMessages as $msg): ?>
            <div class="message-card">
                <div class="message-header">
                    <div class="message-sender">
                        <div class="sender-avatar">
                            <?php echo strtoupper(substr($msg['name'], 0, 2)); ?>
                        </div>
                        <div class="sender-info">
                            <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                            <small><?php echo htmlspecialchars($msg['email']); ?> • <?php echo $msg['phone']; ?></small>
                        </div>
                    </div>
                    <div class="message-meta">
                        <?php
                        $statusClass = [
                            'new' => 'admin-badge-warning',
                            'replied' => 'admin-badge-success',
                            'archived' => 'admin-badge-secondary'
                        ];
                        $statusLabels = [
                            'new' => 'Mới',
                            'replied' => 'Đã trả lời',
                            'archived' => 'Đã lưu trữ'
                        ];
                        ?>
                        <span class="admin-badge <?php echo $statusClass[$msg['status']]; ?>">
                            <?php echo $statusLabels[$msg['status']]; ?>
                        </span>
                        <small><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></small>
                    </div>
                </div>
                <div class="message-subject">
                    <strong><?php echo htmlspecialchars($msg['subject']); ?></strong>
                </div>
                <div class="message-content">
                    <?php echo htmlspecialchars($msg['message']); ?>
                </div>
                <div class="message-actions">
                    <?php if ($msg['status'] === 'new'): ?>
                    <button class="action-btn btn-reply" onclick="replyMessage(<?php echo $msg['id']; ?>)">
                        <i class="fas fa-reply"></i> Trả lời
                    </button>
                    <?php endif; ?>
                    <button class="action-btn btn-archive" onclick="archiveMessage(<?php echo $msg['id']; ?>)">
                        <i class="fas fa-archive"></i> Lưu trữ
                    </button>
                    <button class="action-btn btn-delete" onclick="deleteMessage(<?php echo $msg['id']; ?>)">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.messages-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.message-card {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 20px;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.message-sender {
    display: flex;
    gap: 12px;
    align-items: center;
}

.sender-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #356DF1;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}

.sender-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.sender-info strong {
    color: #1F2937;
    font-size: 15px;
}

.sender-info small {
    color: #6B7280;
    font-size: 13px;
}

.message-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
}

.message-subject {
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #F3F4F6;
}

.message-subject strong {
    color: #1F2937;
    font-size: 15px;
}

.message-content {
    color: #374151;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.message-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-reply {
    color: #356DF1;
    border-color: #356DF1;
}

.btn-reply:hover {
    background: #356DF1;
    color: white;
}

.btn-archive {
    color: #6B7280;
}

.btn-archive:hover {
    background: #F3F4F6;
}

.btn-delete {
    color: #EF4444;
    border-color: #EF4444;
}

.btn-delete:hover {
    background: #EF4444;
    color: white;
}

.admin-badge-secondary {
    background: #F3F4F6;
    color: #6B7280;
}
</style>

<script>
function replyMessage(id) {
    alert('Chức năng trả lời sẽ được triển khai với email integration');
}

function archiveMessage(id) {
    if (confirm('Lưu trữ tin nhắn này?')) {
        alert('Chức năng sẽ được triển khai với backend');
    }
}

function deleteMessage(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')) {
        alert('Chức năng sẽ được triển khai với backend');
    }
}
</script>
