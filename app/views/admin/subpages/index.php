<?php
/**
 * Admin Sub Pages List
 * Lists all dynamic subpages and footer socials with edit actions
 */

require_once __DIR__ . '/../../../models/SubPageModel.php';
$subPageModel = new SubPageModel();

// Get all subpages
$pages = $subPageModel->getAllPages();

// Fallback to defaults if table is empty or query failed
if (empty($pages)) {
    // Seed default subpages automatically to prevent empty list
    $subPageModel->seedDefaultSubPages();
    $pages = $subPageModel->getAllPages();
}

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
?>

<div class="subpages-page" style="padding: 24px; background: #f9fafb; min-height: 100vh;">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
        <div class="page-header-left">
            <h1 class="page-title" style="font-size: 24px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 10px; margin: 0;">
                <i class="fas fa-copy" style="color: #4f46e5;"></i>
                Quản Lý Trang Phụ & Cấu Hình
            </h1>
            <p class="page-description" style="font-size: 14px; color: #6b7280; margin: 4px 0 0 0;">Cấu hình nội dung trang tĩnh, chính sách dịch vụ và mạng xã hội chân trang (Footer)</p>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_msg === 'updated'): ?>
        <div class="alert alert-success" style="background-color: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="font-size: 18px;"></i>
            <div>
                <strong>Thành công!</strong> Đã cập nhật cấu hình/nội dung trang phụ thành công.
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger" style="background-color: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
            <div>
                <strong>Lỗi:</strong> <?= htmlspecialchars($error_msg) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contents Table -->
    <div class="table-container" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); overflow: hidden; border: 1px solid #e5e7eb;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f3f4f6; border-bottom: 1px solid #e5e7eb;">
                    <th style="padding: 14px 16px; font-weight: 600; color: #374151; font-size: 13px; width: 60px;">STT</th>
                    <th style="padding: 14px 16px; font-weight: 600; color: #374151; font-size: 13px; width: 250px;">Tên trang / Loại cấu hình</th>
                    <th style="padding: 14px 16px; font-weight: 600; color: #374151; font-size: 13px; width: 180px;">Mã trang (Key)</th>
                    <th style="padding: 14px 16px; font-weight: 600; color: #374151; font-size: 13px;">Chi tiết cấu hình / Tiêu đề SEO</th>
                    <th style="padding: 14px 16px; font-weight: 600; color: #374151; font-size: 13px; width: 160px;">Cập nhật cuối</th>
                    <th style="padding: 14px 16px; font-weight: 600; color: #374151; font-size: 13px; width: 120px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                    <tr>
                        <td colspan="6" class="no-data" style="padding: 40px; text-align: center; color: #9ca3af;">
                            <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 12px;"></i>
                            <p style="margin: 0;">Không tìm thấy trang phụ nào. Vui lòng kiểm tra lại CSDL hoặc chạy migration.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $stt = 1; 
                    foreach ($pages as $page): 
                        $isSocial = ($page['page_key'] === 'footer_socials');
                    ?>
                        <tr style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 16px; font-size: 14px; color: #4b5563; font-weight: 500;"><?= $stt++ ?></td>
                            <td style="padding: 16px;">
                                <div class="page-title-cell" style="font-weight: 600; color: #111827; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                    <?php if ($isSocial): ?>
                                        <i class="fas fa-share-alt" style="color: #3b82f6;"></i>
                                    <?php else: ?>
                                        <i class="far fa-file-alt" style="color: #4b5563;"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($page['title']) ?>
                                </div>
                            </td>
                            <td style="padding: 16px;">
                                <code style="background: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #ef4444; font-weight: 600; border: 1px solid #e5e7eb;">
                                    <?= htmlspecialchars($page['page_key']) ?>
                                </code>
                            </td>
                            <td style="padding: 16px;">
                                <?php if ($isSocial): ?>
                                    <div class="social-pills" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                        <?php 
                                        $socialData = json_decode($page['content'], true);
                                        if (is_array($socialData)):
                                            foreach ($socialData as $sKey => $sVal):
                                                $visible = $sVal['visible'] ?? true;
                                                $opacity = $visible ? '1' : '0.4';
                                                $icon = $sVal['icon'] ?? 'fab fa-link';
                                                $url = $sVal['url'] ?? '#';
                                        ?>
                                                <span class="social-pill" style="display: inline-flex; align-items: center; gap: 4px; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 9999px; font-size: 12px; opacity: <?= $opacity ?>;" title="<?= htmlspecialchars($url) ?>">
                                                    <i class="<?= htmlspecialchars($icon) ?>"></i>
                                                    <?= htmlspecialchars($sVal['name'] ?? $sKey) ?>
                                                    <?php if (!$visible): ?>
                                                        <i class="far fa-eye-slash" style="color: #94a3b8; font-size: 10px;" title="Ẩn"></i>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: #9ca3af; font-size: 13px; font-style: italic;">Chưa thiết lập cấu hình</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="seo-title" style="color: #4b5563; font-size: 13px; display: block; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 450px;" title="<?= htmlspecialchars($page['meta_title'] ?? '') ?>">
                                        <?= htmlspecialchars($page['meta_title'] ?? 'Chưa cấu hình tiêu đề SEO') ?>
                                    </span>
                                    <span class="seo-desc" style="color: #9ca3af; font-size: 11px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 450px; margin-top: 2px;" title="<?= htmlspecialchars($page['meta_description'] ?? '') ?>">
                                        <?= htmlspecialchars($page['meta_description'] ?? 'Chưa cấu hình mô tả SEO') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 16px;">
                                <span style="font-size: 13px; color: #6b7280; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                                    <i class="far fa-clock" style="color: #9ca3af;"></i>
                                    <?= date('d/m/Y H:i', strtotime($page['updated_at'])) ?>
                                </span>
                            </td>
                            <td style="padding: 16px; text-align: center;">
                                <a href="?page=admin&module=subpages&action=edit&key=<?= $page['page_key'] ?>" 
                                   class="btn btn-sm" 
                                   style="display: inline-flex; align-items: center; gap: 6px; background: #f59e0b; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px; text-decoration: none; border: none; cursor: pointer; transition: background 0.2s;"
                                   onmouseover="this.style.backgroundColor='#d97706'"
                                   onmouseout="this.style.backgroundColor='#f59e0b'"
                                   title="Chỉnh sửa nội dung / cấu hình">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
