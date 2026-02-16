<?php
/**
 * Admin Agent Management View
 * Requirements: 3.1, 3.2
 */

// Ensure we have the required data
$activeUsers = $activeUsers ?? [];
$pendingAgentRequests = $pendingAgentRequests ?? [];
$approvedAgents = $approvedAgents ?? [];
?>

<div class="admin-content">
    <div class="page-header">
        <h1>Quản lý đại lý</h1>
        <p class="page-description">Quản lý người dùng và yêu cầu đăng ký đại lý</p>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" data-tab="users">Người dùng</button>
        <button class="tab-btn" data-tab="agents">Đại lý</button>
    </div>

    <!-- Users Tab -->
    <div id="users-tab" class="tab-content active">
        <div class="section-header">
            <h2>Người dùng hoạt động</h2>
            <p>Danh sách người dùng với vai trò "người dùng" và trạng thái "hoạt động"</p>
        </div>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activeUsers)): ?>
                        <tr>
                            <td colspan="6" class="no-data">Không có người dùng nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($activeUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="role-badge role-user">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-active">
                                        <?= htmlspecialchars($user['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Agents Tab -->
    <div id="agents-tab" class="tab-content">
        <div class="section-header">
            <h2>Yêu cầu đăng ký đại lý</h2>
            <p>Danh sách yêu cầu đăng ký đại lý chờ duyệt và đã được phê duyệt</p>
        </div>

        <!-- Pending Requests -->
        <div class="subsection">
            <h3>Chờ duyệt</h3>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Trạng thái yêu cầu</th>
                            <th>Ngày yêu cầu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingAgentRequests)): ?>
                            <tr>
                                <td colspan="6" class="no-data">Không có yêu cầu nào đang chờ duyệt</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingAgentRequests as $request): ?>
                                <tr data-request-id="<?= $request['id'] ?>">
                                    <td><?= htmlspecialchars($request['id']) ?></td>
                                    <td><?= htmlspecialchars($request['name']) ?></td>
                                    <td><?= htmlspecialchars($request['email']) ?></td>
                                    <td>
                                        <span class="status-badge status-pending">
                                            <?= htmlspecialchars($request['agent_request_status']) ?>
                                        </span>
                                    </td>
                                    <td><?= $request['agent_request_date'] ? date('d/m/Y H:i', strtotime($request['agent_request_date'])) : 'N/A' ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-success approve-btn" data-request-id="<?= $request['id'] ?>">
                                            Phê duyệt
                                        </button>
                                        <button class="btn btn-danger reject-btn" data-request-id="<?= $request['id'] ?>">
                                            Từ chối
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Approved Agents -->
        <div class="subsection">
            <h3>Đại lý đã phê duyệt</h3>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày phê duyệt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($approvedAgents)): ?>
                            <tr>
                                <td colspan="6" class="no-data">Chưa có đại lý nào được phê duyệt</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($approvedAgents as $agent): ?>
                                <tr>
                                    <td><?= htmlspecialchars($agent['id']) ?></td>
                                    <td><?= htmlspecialchars($agent['name']) ?></td>
                                    <td><?= htmlspecialchars($agent['email']) ?></td>
                                    <td>
                                        <span class="role-badge role-agent">
                                            <?= htmlspecialchars($agent['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-approved">
                                            <?= htmlspecialchars($agent['agent_request_status']) ?>
                                        </span>
                                    </td>
                                    <td><?= $agent['agent_approved_date'] ? date('d/m/Y H:i', strtotime($agent['agent_approved_date'])) : 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="loading-spinner"></div>
        <p>Đang xử lý...</p>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3 id="confirmation-title">Xác nhận</h3>
        <p id="confirmation-message">Bạn có chắc chắn muốn thực hiện hành động này?</p>
        <div class="modal-actions">
            <button id="confirm-btn" class="btn btn-primary">Xác nhận</button>
            <button id="cancel-btn" class="btn btn-secondary">Hủy</button>
        </div>
    </div>
</div>

<style>
.admin-content {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    color: #333;
    margin-bottom: 5px;
}

.page-description {
    color: #666;
    margin: 0;
}

.tab-navigation {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab-btn {
    background: none;
    border: none;
    padding: 12px 24px;
    cursor: pointer;
    font-size: 16px;
    color: #666;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: #007bff;
    border-bottom-color: #007bff;
}

.tab-btn:hover {
    color: #007bff;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.section-header {
    margin-bottom: 20px;
}

.section-header h2 {
    color: #333;
    margin-bottom: 5px;
}

.subsection {
    margin-bottom: 40px;
}

.subsection h3 {
    color: #555;
    margin-bottom: 15px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e0e0e0;
}

.table-container {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.admin-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.admin-table tr:hover {
    background-color: #f8f9fa;
}

.no-data {
    text-align: center;
    color: #666;
    font-style: italic;
}

.role-badge,
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.role-user {
    background-color: #e3f2fd;
    color: #1976d2;
}

.role-agent {
    background-color: #f3e5f5;
    color: #7b1fa2;
}

.status-active {
    background-color: #e8f5e8;
    color: #2e7d32;
}

.status-pending {
    background-color: #fff3e0;
    color: #f57c00;
}

.status-approved {
    background-color: #e8f5e8;
    color: #2e7d32;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .admin-content {
        padding: 10px;
    }
    
    .tab-btn {
        padding: 10px 16px;
        font-size: 14px;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 8px;
        font-size: 14px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all tabs and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab + '-tab').classList.add('active');
        });
    });
    
    // Agent approval/rejection
    const approveButtons = document.querySelectorAll('.approve-btn');
    const rejectButtons = document.querySelectorAll('.reject-btn');
    const loadingModal = document.getElementById('loading-modal');
    const confirmationModal = document.getElementById('confirmation-modal');
    const confirmBtn = document.getElementById('confirm-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    
    let currentAction = null;
    let currentRequestId = null;
    
    approveButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            currentRequestId = this.dataset.requestId;
            currentAction = 'approve';
            
            document.getElementById('confirmation-title').textContent = 'Phê duyệt đại lý';
            document.getElementById('confirmation-message').textContent = 'Bạn có chắc chắn muốn phê duyệt yêu cầu này?';
            confirmationModal.style.display = 'flex';
        });
    });
    
    rejectButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            currentRequestId = this.dataset.requestId;
            currentAction = 'reject';
            
            document.getElementById('confirmation-title').textContent = 'Từ chối đại lý';
            document.getElementById('confirmation-message').textContent = 'Bạn có chắc chắn muốn từ chối yêu cầu này?';
            confirmationModal.style.display = 'flex';
        });
    });
    
    confirmBtn.addEventListener('click', function() {
        if (currentAction && currentRequestId) {
            confirmationModal.style.display = 'none';
            loadingModal.style.display = 'flex';
            
            const formData = new FormData();
            formData.append('request_id', currentRequestId);
            formData.append('status', currentAction === 'approve' ? 'approved' : 'rejected');
            
            fetch('/admin/agents/update-status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingModal.style.display = 'none';
                
                if (data.success) {
                    // Remove the row from pending requests table
                    const row = document.querySelector(`tr[data-request-id="${currentRequestId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Show success message
                    alert(data.message);
                    
                    // Reload page to update the approved agents list
                    window.location.reload();
                } else {
                    alert('Lỗi: ' + (data.error || 'Có lỗi xảy ra'));
                }
            })
            .catch(error => {
                loadingModal.style.display = 'none';
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xử lý yêu cầu');
            });
        }
    });
    
    cancelBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
        currentAction = null;
        currentRequestId = null;
    });
    
    // Close modals when clicking outside
    [loadingModal, confirmationModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                currentAction = null;
                currentRequestId = null;
            }
        });
    });
});
</script>