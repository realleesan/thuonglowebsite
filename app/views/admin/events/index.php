<?php
// Professional Events Management
$page_title = "Quản lý Sự kiện";
$breadcrumb = [
    ['text' => 'Dashboard', 'url' => '?page=admin&module=dashboard'],
    ['text' => 'Sự kiện', 'url' => null]
];

// Load fake data
$fake_data_file = __DIR__ . '/../data/fake_data.json';
$allEvents = [];

if (file_exists($fake_data_file)) {
    $json_data = json_decode(file_get_contents($fake_data_file), true);
    $allEvents = $json_data['events'] ?? [];
}

// Get filter parameters
$filterStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Apply filters
$events = $allEvents;

if ($searchQuery) {
    $events = array_filter($events, function($e) use ($searchQuery) {
        return stripos($e['title'], $searchQuery) !== false || 
               stripos($e['description'], $searchQuery) !== false;
    });
}

if ($filterStatus) {
    $events = array_filter($events, function($e) use ($filterStatus) {
        return $e['status'] === $filterStatus;
    });
}

// Sort by event date
usort($events, function($a, $b) {
    return strtotime($b['event_date']) - strtotime($a['event_date']);
});

// Stats
$stats = [
    'total' => count($allEvents),
    'upcoming' => count(array_filter($allEvents, function($e) { return $e['status'] === 'upcoming'; })),
    'completed' => count(array_filter($allEvents, function($e) { return $e['status'] === 'completed'; })),
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
    <div class="page-header-right">
        <a href="?page=admin&module=events&action=change" class="admin-btn admin-btn-primary">
            <i class="fas fa-plus"></i> Thêm sự kiện mới
        </a>
    </div>
</div>

<!-- Stats Summary -->
<div class="admin-stats-summary">
    <div class="stat-item">
        <span class="stat-label">Tổng cộng:</span>
        <span class="stat-value"><?php echo $stats['total']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Sắp diễn ra:</span>
        <span class="stat-value text-success"><?php echo $stats['upcoming']; ?></span>
    </div>
    <div class="stat-item">
        <span class="stat-label">Đã hoàn thành:</span>
        <span class="stat-value text-muted"><?php echo $stats['completed']; ?></span>
    </div>
</div>

<!-- Filters -->
<div class="admin-filters-bar">
    <form method="GET" action="" class="filters-form">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="module" value="events">
        
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Tìm kiếm sự kiện..." 
                   value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
        </div>
        
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">Tất cả trạng thái</option>
                <option value="upcoming" <?php echo $filterStatus === 'upcoming' ? 'selected' : ''; ?>>Sắp diễn ra</option>
                <option value="ongoing" <?php echo $filterStatus === 'ongoing' ? 'selected' : ''; ?>>Đang diễn ra</option>
                <option value="completed" <?php echo $filterStatus === 'completed' ? 'selected' : ''; ?>>Đã hoàn thành</option>
            </select>
        </div>
        
        <button type="submit" class="admin-btn admin-btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        
        <?php if ($searchQuery || $filterStatus): ?>
        <a href="?page=admin&module=events" class="admin-btn admin-btn-secondary">
            <i class="fas fa-times"></i> Xóa bộ lọc
        </a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($events)): ?>
            <div class="admin-empty-state">
                <i class="fas fa-calendar-alt" style="font-size: 48px; color: #9CA3AF; margin-bottom: 16px;"></i>
                <h3>Không tìm thấy sự kiện</h3>
                <p>Thử thay đổi bộ lọc hoặc thêm sự kiện mới</p>
                <a href="?page=admin&module=events&action=change" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Thêm sự kiện
                </a>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-date-badge">
                        <div class="date-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                        <div class="date-month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                    </div>
                    <div class="event-content">
                        <div class="event-header">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <span class="event-status status-<?php echo $event['status']; ?>">
                                <?php 
                                $statusLabels = [
                                    'upcoming' => 'Sắp diễn ra',
                                    'ongoing' => 'Đang diễn ra',
                                    'completed' => 'Đã hoàn thành'
                                ];
                                echo $statusLabels[$event['status']] ?? $event['status'];
                                ?>
                            </span>
                        </div>
                        <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 120)) . '...'; ?></p>
                        <div class="event-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($event['event_date'])); ?></span>
                        </div>
                    </div>
                    <div class="event-actions">
                        <a href="?page=admin&module=events&action=edit&id=<?php echo $event['id']; ?>" 
                           class="admin-btn admin-btn-sm admin-btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?page=admin&module=events&action=delete&id=<?php echo $event['id']; ?>" 
                           class="admin-btn admin-btn-sm admin-btn-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.event-card {
    display: flex;
    gap: 16px;
    padding: 20px;
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.event-card:hover {
    border-color: #356DF1;
    box-shadow: 0 4px 12px rgba(53, 109, 241, 0.1);
    transform: translateY(-2px);
}

.event-date-badge {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    background: #356DF1;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
}

.date-day {
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
}

.date-month {
    font-size: 12px;
    text-transform: uppercase;
    margin-top: 4px;
}

.event-content {
    flex: 1;
}

.event-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
}

.event-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1F2937;
    flex: 1;
}

.event-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.status-upcoming {
    background: #DBEAFE;
    color: #1E40AF;
}

.status-ongoing {
    background: #D1FAE5;
    color: #065F46;
}

.status-completed {
    background: #F3F4F6;
    color: #6B7280;
}

.event-description {
    margin: 0 0 12px 0;
    font-size: 13px;
    color: #6B7280;
    line-height: 1.5;
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 12px;
    color: #9CA3AF;
}

.event-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.event-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (max-width: 768px) {
    .events-grid {
        grid-template-columns: 1fr;
    }
}
</style>