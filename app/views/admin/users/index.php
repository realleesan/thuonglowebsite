<?php
// Load fake data for users
$dataFile = __DIR__ . '/../data/fake_data.json';
$data = [];

if (file_exists($dataFile)) {
    $jsonContent = file_get_contents($dataFile);
    $data = json_decode($jsonContent, true) ?: [];
}

$users = $data['users'] ?? [];
?>

<div class="admin-users">
    <div class="page-header">
        <h2>Quản lý người dùng</h2>
        <div class="page-actions">
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm người dùng
            </button>
        </div>
    </div>

    <div class="users-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="no-data">Chưa có người dùng nào</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Quản trị' : 'Người dùng'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Tạm khóa'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Xóa">
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
</div>