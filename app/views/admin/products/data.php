<?php
/**
 * Admin Product Data Management
 * Upload Excel, Manual Entry, Edit/Delete Data
 */

// Initialize View
require_once __DIR__ . '/../../../../core/view_init.php';

// Get service
$service = isset($currentService) ? $currentService : ($adminService ?? null);

// Get product ID
$product_id = (int)($_GET['product_id'] ?? $_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: ?page=admin&module=products&error=invalid_id');
    exit;
}

// Initialize models
$productDataModel = new ProductDataModel();
$productsModel = new ProductsModel();

// Get product info
$product = $productsModel->find($product_id);

if (!$product) {
    header('Location: ?page=admin&module=products&error=not_found');
    exit;
}

// Handle actions
$message = '';
$messageType = '';

// Upload Excel
if (isset($_POST['action']) && $_POST['action'] === 'upload_excel') {
    if (!empty($_FILES['excel_file']['tmp_name'])) {
        require_once __DIR__ . '/../../services/ExcelParserService.php';
        $parser = new ExcelParserService();
        
        $result = $parser->parse($_FILES['excel_file']['tmp_name']);
        
        if ($result['success']) {
            // Delete old data and insert new
            $productDataModel->deleteByProduct($product_id);
            $inserted = $productDataModel->bulkInsert($result['data']);
            
            $message = "Đã upload thành công {$inserted} dòng dữ liệu!";
            $messageType = 'success';
            
            if (!empty($result['warnings'])) {
                $message .= " (" . count($result['warnings']) . " cảnh báo)";
            }
        } else {
            $message = $result['error'];
            $messageType = 'error';
        }
    } else {
        $message = 'Vui lòng chọn file để upload';
        $messageType = 'error';
    }
}

// Add Manual Entry
if (isset($_POST['action']) && $_POST['action'] === 'add_manual') {
    $data = [
        'product_id' => $product_id,
        'supplier_name' => trim($_POST['supplier_name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'wechat_account' => trim($_POST['wechat_account'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
    ];
    
    if (empty($data['supplier_name'])) {
        $message = 'Tên nhà cung cấp không được để trống';
        $messageType = 'error';
    } elseif (empty($data['wechat_account'])) {
        $message = 'Tài khoản WeChat không được để trống';
        $messageType = 'error';
    } else {
        $productDataModel->create($data);
        $message = 'Đã thêm dữ liệu thành công!';
        $messageType = 'success';
    }
}

// Delete row
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['data_id'])) {
    $dataId = (int)$_GET['data_id'];
    $productDataModel->delete($dataId);
    $message = 'Đã xóa dữ liệu!';
    $messageType = 'success';
}

// Update row
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $dataId = (int)$_POST['data_id'];
    $data = [
        'supplier_name' => trim($_POST['supplier_name'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'wechat_account' => trim($_POST['wechat_account'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
    ];
    
    if (!empty($data['supplier_name']) && !empty($data['wechat_account'])) {
        $productDataModel->update($dataId, $data);
        $message = 'Đã cập nhật dữ liệu!';
        $messageType = 'success';
    }
}

// Get pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$dataPaginated = $productDataModel->getByProductPaginated($product_id, $page, $perPage);
$dataList = $dataPaginated['data'];
$pagination = $dataPaginated;

// Get edit data if requested
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['data_id'])) {
    $editData = $productDataModel->find((int)$_GET['data_id']);
}
?>

<?php include __DIR__ . '/../_layout/admin_header.php'; ?>

<style>
.data-management {
    padding: 20px;
}
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}
.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}
.product-info {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.product-info strong {
    color: #007bff;
}
.alert {
    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.upload-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.section-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}
.form-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}
.form-group {
    flex: 1;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #555;
}
.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}
.btn-primary {
    background: #007bff;
    color: #fff;
}
.btn-primary:hover {
    background: #0056b3;
}
.btn-success {
    background: #28a745;
    color: #fff;
}
.btn-success:hover {
    background: #1e7e34;
}
.btn-danger {
    background: #dc3545;
    color: #fff;
}
.btn-danger:hover {
    background: #c82333;
}
.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}
.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th,
.data-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}
.data-table tr:hover {
    background: #f8f9fa;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}
.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}
.pagination a:hover {
    background: #007bff;
    color: #fff;
}
.pagination .active {
    background: #007bff;
    color: #fff;
}
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}
.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}
.modal-title {
    font-size: 18px;
    font-weight: 600;
}
.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}
.stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}
.stat-box {
    background: #e7f3ff;
    padding: 15px 20px;
    border-radius: 8px;
    text-align: center;
}
.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #007bff;
}
.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}
</style>

<div class="data-management">
    <div class="page-header">
        <h1 class="page-title">Quản lý dữ liệu sản phẩm</h1>
        <a href="?page=admin&module=products&action=edit&id=<?php echo $product_id; ?>" class="btn btn-primary">
            ← Quay lại sản phẩm
        </a>
    </div>
    
    <div class="product-info">
        <strong>Sản phẩm:</strong> <?php echo htmlspecialchars($product['name'] ?? ''); ?>
    </div>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
    
    <div class="stats">
        <div class="stat-box">
            <div class="stat-number"><?php echo $pagination['total'] ?? 0; ?></div>
            <div class="stat-label">Tổng dữ liệu</div>
        </div>
    </div>
    
    <!-- Upload Excel Section -->
    <div class="upload-section">
        <h3 class="section-title">📤 Upload Excel/CSV</h3>
        <form method="POST" enctype="multipart/form-data" class="form-row">
            <input type="hidden" name="action" value="upload_excel">
            <div class="form-group">
                <label for="excel_file">Chọn file (.xlsx, .xls, .csv):</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload & Thay thế</button>
        </form>
        <p style="margin-top: 10px; font-size: 12px; color: #666;">
            ⚠️ Lưu ý: Upload file mới sẽ xóa toàn bộ dữ liệu cũ và thay bằng dữ liệu mới.<br>
            Template: Tên nhà cung cấp, Địa chỉ, Tài khoản WeChat, Số điện thoại, QR WeChat (URL)
        </p>
    </div>
    
    <!-- Manual Entry Section -->
    <div class="upload-section">
        <h3 class="section-title">➕ Thêm thủ công</h3>
        <form method="POST" class="form-row">
            <input type="hidden" name="action" value="add_manual">
            <div class="form-group">
                <label>Tên NCC:</label>
                <input type="text" name="supplier_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Địa chỉ:</label>
                <input type="text" name="address" class="form-control">
            </div>
            <div class="form-group">
                <label>WeChat:</label>
                <input type="text" name="wechat_account" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Điện thoại:</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group">
                <label>QR URL:</label>
                <input type="text" name="wechat_qr" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Thêm</button>
        </form>
    </div>
    
    <!-- Data List Section -->
    <div class="table-container">
        <h3 class="section-title">📋 Danh sách dữ liệu (Trang <?php echo $pagination['current_page']; ?>/<?php echo $pagination['last_page']; ?>)</h3>
        
        <?php if (empty($dataList)): ?>
        <p style="text-align: center; color: #999; padding: 20px;">
            Chưa có dữ liệu. Vui lòng upload Excel hoặc thêm thủ công.
        </p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Địa chỉ</th>
                    <th>WeChat</th>
                    <th>Điện thoại</th>
                    <th>QR</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $startIndex = ($pagination['current_page'] - 1) * $pagination['per_page'];
                foreach ($dataList as $index => $row): 
                ?>
                <tr>
                    <td><?php echo $startIndex + $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['address'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['wechat_account'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                    <td>
                        <?php if (!empty($row['wechat_qr'])): ?>
                        <a href="<?php echo htmlspecialchars($row['wechat_qr']); ?>" target="_blank">Xem QR</a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?page=admin&module=products&action=manage_data&product_id=<?php echo $product_id; ?>&action=edit&data_id=<?php echo $row['id']; ?>" 
                           class="btn btn-primary btn-sm">Sửa</a>
                        <a href="?page=admin&module=products&action=manage_data&product_id=<?php echo $product_id; ?>&action=delete&data_id=<?php echo $row['id']; ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($pagination['last_page'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
            <a href="?page=admin&module=products&action=manage_data&product_id=<?php echo $product_id; ?>&page=<?php echo $i; ?>" 
               class="<?php echo ($i === $pagination['current_page']) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>

<?php if ($editData): ?>
editData): ?>
<div class="modal show" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Sửa dữ liệu</h3>
            <button class="close-btn" onclick="window.location.href='?page=admin&module=products&action=manage_data&product_id=<?php echo $product_id; ?>'">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="data_id" value="<?php echo $editData['id']; ?>">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Tên nhà cung cấp:</label>
                <input type="text" name="supplier_name" class="form-control" value="<?php echo htmlspecialchars($editData['supplier_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Địa chỉ:</label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($editData['address'] ?? ''); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Tài khoản WeChat:</label>
                <input type="text" name="wechat_account" class="form-control" value="<?php echo htmlspecialchars($editData['wechat_account'] ?? ''); ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Số điện thoại:</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($editData['phone'] ?? ''); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>QR WeChat URL:</label>
                <input type="text" name="wechat_qr" class="form-control" value="<?php echo htmlspecialchars($editData['wechat_qr'] ?? ''); ?>">
            </div>
            
            <button type="submit" class="btn btn-success" style="width: 100%;">Lưu thay đổi</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../_layout/admin_footer.php'; ?>
