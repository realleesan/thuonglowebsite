<?php
/**
 * Admin Affiliate Content List
 * Lists all dynamic agent pages with edit actions
 */

// Choose admin service
$service = isset($currentService) ? $currentService : ($adminService ?? null);

if ($service === null) {
    die('Error: AdminService not available.');
}

require_once __DIR__ . '/../../../models/AgentContentModel.php';
$agentContentModel = new AgentContentModel();
$contents = $agentContentModel->getAllContents();

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
?>

<div class="affiliates-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-file-alt"></i>
                Quản Lý Nội Dung Đại Lý
            </h1>
            <p class="page-description">Chỉnh sửa nội dung, hình ảnh các trang giới thiệu và hướng dẫn dành cho đại lý</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_msg === 'updated'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Thành công!</strong> Đã cập nhật nội dung trang đại lý thành công.
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Lỗi:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        </div>
    <?php endif; ?>


    <!-- Contents Table -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="80">STT</th>
                    <th width="250">Tên trang nội dung</th>
                    <th>Slug / Mã trang</th>
                    <th width="300">Tiêu đề SEO</th>
                    <th width="150">Cập nhật lúc</th>
                    <th width="120">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contents)): ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy trang nội dung nào. Vui lòng chạy database seed.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $stt = 1; foreach ($contents as $page): ?>
                        <tr>
                            <td><?= $stt++ ?></td>
                            <td>
                                <div class="page-title-cell" style="font-weight: 600; color: #1f2937;">
                                    <?= htmlspecialchars($page['title']) ?>
                                </div>
                            </td>
                            <td>
                                <code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-family: monospace; font-size: 13px; color: #ef4444;">
                                    <?= htmlspecialchars($page['page_key']) ?>
                                </code>
                            </td>
                            <td>
                                <span class="seo-title" style="color: #4b5563; font-size: 13px;">
                                    <?= htmlspecialchars($page['meta_title'] ?? 'Chưa cấu hình') ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 13px; color: #6b7280;">
                                    <?= date('d/m/Y H:i', strtotime($page['updated_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=admin&module=affiliates&action=content_edit&key=<?= $page['page_key'] ?>" 
                                       class="btn btn-sm btn-warning" title="Chỉnh sửa nội dung" style="display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-edit"></i> Sửa trang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
