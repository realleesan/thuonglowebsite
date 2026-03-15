<?php
/**
 * Admin Products Data Management Page
 * Flow: Product List -> Select Product -> Data Management
 */

// Get parameters from URL
$search = $_GET['search'] ?? '';
$categoryFilter = (int)($_GET['category'] ?? 0);
$selectedProductId = (int)($_GET['id'] ?? 0); // Using 'id' parameter for selected product
$page = max(1, (int)($_GET['page'] ?? 1));
$activeTab = $_GET['tab'] ?? 'manual';
$perPage = 20;

// Try to load models safely
$products = [];
$categories = [];
$selectedProduct = null;
$dataList = [];
$dataCount = 0;
$dataPaginated = null;
$totalPages = 1;
$errorMessage = '';

try {
    // Include models
    $modelPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/models/';
    
    if (file_exists($modelPath . 'ProductsModel.php')) {
        require_once $modelPath . 'ProductsModel.php';
        $productsModel = new ProductsModel();
    }
    
    if (file_exists($modelPath . 'CategoriesModel.php')) {
        require_once $modelPath . 'CategoriesModel.php';
        $categoriesModel = new CategoriesModel();
    }
    
    if (file_exists($modelPath . 'ProductDataModel.php')) {
        require_once $modelPath . 'ProductDataModel.php';
        $productDataModel = new ProductDataModel();
    }
    
    // Get categories
    if (isset($categoriesModel)) {
        $categories = $categoriesModel->getActive();
    }
    
    // If no product selected, show product list
    if ($selectedProductId === 0) {
        // Build products query for list view
        if (isset($productsModel)) {
            $productsQuery = "SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE 1=1";
            $countQuery = "SELECT COUNT(*) as total FROM products p WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $productsQuery .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
                $countQuery .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            if ($categoryFilter > 0) {
                $productsQuery .= " AND p.category_id = ?";
                $countQuery .= " AND p.category_id = ?";
                $params[] = $categoryFilter;
            }

            $productsQuery .= " ORDER BY p.id DESC LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage);
            
            $products = $productsModel->query($productsQuery, $params);
            
            // Get total
            $countResult = $productsModel->query($countQuery, $params);
            $totalProducts = $countResult[0]['total'] ?? 0;
            $totalPages = ceil($totalProducts / $perPage);
        }
    } else {
        // Show data management for selected product
        if (isset($productsModel)) {
            $selectedProducts = $productsModel->query("SELECT p.*, c.name as category_name 
                                                        FROM products p 
                                                        LEFT JOIN categories c ON p.category_id = c.id 
                                                        WHERE p.id = ?", [$selectedProductId]);
            $selectedProduct = !empty($selectedProducts) ? $selectedProducts[0] : null;
            
            if ($selectedProduct && isset($productDataModel)) {
                $dataCount = $productDataModel->countByProduct($selectedProductId);
                
                $dmPage = max(1, (int)($_GET['dm_page'] ?? 1));
                $dmPerPage = 10;
                $dataPaginated = $productDataModel->getByProductPaginated($selectedProductId, $dmPage, $dmPerPage);
                $dataList = $dataPaginated['data'] ?? [];
            }
        }
        
        // Handle form actions
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data_action']) && isset($productDataModel)) {
            $action = $_POST['data_action'];
            
            switch ($action) {
                case 'add_manual':
                    $data = [
                        'product_id' => $selectedProductId,
                        'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                        'address' => trim($_POST['address'] ?? ''),
                        'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                        'phone' => trim($_POST['phone'] ?? ''),
                        'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
                    ];
                    $productDataModel->create($data);
                    $message = 'Đã thêm dữ liệu thành công!';
                    $messageType = 'success';
                    break;
                    
                case 'update_row':
                    if (!empty($_POST['data_id'])) {
                        $dataId = (int)$_POST['data_id'];
                        $data = [
                            'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                            'address' => trim($_POST['address'] ?? ''),
                            'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                            'phone' => trim($_POST['phone'] ?? ''),
                            'wechat_qr' => trim($_POST['wechat_qr'] ?? '')
                        ];
                        $productDataModel->update($dataId, $data);
                        $message = 'Đã cập nhật dữ liệu!';
                        $messageType = 'success';
                    }
                    break;
                    
                case 'delete_row':
                    if (!empty($_POST['data_id'])) {
                        $productDataModel->delete((int)$_POST['data_id']);
                        $message = 'Đã xóa dữ liệu!';
                        $messageType = 'success';
                    }
                    break;
                    
                case 'delete_all':
                    $productDataModel->deleteByProduct($selectedProductId);
                    $message = 'Đã xóa tất cả dữ liệu!';
                    $messageType = 'success';
                    break;
            }
            
            // Refresh data
            $dataCount = $productDataModel->countByProduct($selectedProductId);
            $dataPaginated = $productDataModel->getByProductPaginated($selectedProductId, $dmPage ?? 1, 10);
            $dataList = $dataPaginated['data'] ?? [];
        }
        
        // Get edit item if requested
        $editDataItem = null;
        if (isset($_GET['dm_action']) && $_GET['dm_action'] === 'edit' && !empty($_GET['dm_data_id']) && isset($productDataModel)) {
            $editDataItem = $productDataModel->find((int)$_GET['dm_data_id']);
            if ($editDataItem) {
                $activeTab = 'manual';
            }
        }
    }
    
} catch (Exception $e) {
    error_log('Data page error: ' . $e->getMessage());
    $errorMessage = $e->getMessage();
}

// Helper function
function getProductDataCount($productId) {
    try {
        $modelPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/models/ProductDataModel.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            $model = new ProductDataModel();
            return $model->countByProduct($productId);
        }
    } catch (Exception $e) {
        error_log('Error getting count: ' . $e->getMessage());
    }
    return 0;
}
?>

<div class="product-data-page">
    <!-- Error Display -->
    <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Lỗi: <?= htmlspecialchars($errorMessage) ?>
    </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1 class="page-title">
                <i class="fas fa-database"></i>
                <?= $selectedProductId > 0 ? 'Quản Lý Dữ Liệu Sản Phẩm' : 'Quản Lý Dữ Liệu Sản Phẩm' ?>
            </h1>
            <p class="page-description">
                <?= $selectedProductId > 0 
                    ? 'Thêm và quản lý dữ liệu bổ sung cho sản phẩm' 
                    : 'Chọn sản phẩm để quản lý dữ liệu bổ sung' ?>
            </p>
        </div>
        <div class="page-header-right">
            <?php if ($selectedProductId > 0): ?>
            <a href="?page=admin&module=products&action=data" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
            <?php else: ?>
            <a href="?page=admin&module=products" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách sản phẩm
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>" id="messageBox">
        <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <script>
        setTimeout(function() {
            var msg = document.getElementById('messageBox');
            if (msg) msg.style.display = 'none';
        }, 3000);
    </script>
    <?php endif; ?>

    <?php if ($selectedProductId === 0): ?>
    <!-- VIEW 1: Product List Only -->
    <div class="section products-panel">
        <div class="section-header">
            <h3 style="padding-left: 20px;"><i class="fas fa-box"></i> Danh Sách Sản Phẩm</h3>
        </div>
        
        <!-- Search & Filter -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="module" value="products">
                <input type="hidden" name="action" value="data">
                
                <div class="filter-row">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." 
                           value="<?= htmlspecialchars($search) ?>" class="search-input">
                    <select name="category" class="category-select">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="products-table-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width:50px">ID</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th style="width:60px">Data</th>
                        <th style="width:100px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                    <?php foreach ($products as $prod): ?>
                    <?php 
                        $prodDataCount = getProductDataCount($prod['id']);
                    ?>
                    <tr>
                        <td><?= $prod['id'] ?></td>
                        <td>
                            <div class="product-cell">
                                <?php if (!empty($prod['image'])): ?>
                                <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" class="product-thumb">
                                <?php endif; ?>
                                <div class="product-info">
                                    <span class="product-name"><?= htmlspecialchars($prod['name']) ?></span>
                                    <?php if (!empty($prod['sku'])): ?>
                                    <span class="product-sku">SKU: <?= htmlspecialchars($prod['sku']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($prod['category_name'] ?? '-') ?></td>
                        <td>
                            <span class="data-count-badge <?= $prodDataCount > 0 ? 'has-data' : '' ?>">
                                <?= $prodDataCount ?>
                            </span>
                        </td>
                        <td>
                            <a href="?page=admin&module=products&action=data&id=<?= $prod['id'] ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state" style="text-align: center;">
                            <i class="fas fa-inbox"></i>
                            <p>Không tìm thấy sản phẩm nào</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=admin&module=products&action=data&page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>" 
               class="<?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- VIEW 2: Data Management for Selected Product -->
    <div class="section data-panel">
        <div class="section-header">
            <h3 style="padding-left: 20px;"><i class="fas fa-database"></i> <?= htmlspecialchars($selectedProduct['name'] ?? '') ?></h3>
        </div>

        <!-- Stats -->
        <div class="data-stats">
            <div class="stat-item">
                <i class="fas fa-list-ol"></i>
                <span class="stat-value"><?= (int)$dataCount ?></span>
                <span class="stat-label">Tổng dữ liệu</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-box"></i>
                <span class="stat-value"><?= (int)($selectedProduct['record_count'] ?? 0) ?></span>
                <span class="stat-label">Record count</span>
            </div>
        </div>

        <!-- Tab Buttons -->
        <div class="import-tabs">
            <button class="import-tab-btn <?= $activeTab === 'manual' ? 'active' : '' ?>" 
                    onclick="switchTab('manual', <?= $selectedProductId ?>)">
                <i class="fas fa-plus-circle"></i> Thêm Thủ Công
            </button>
            <button class="import-tab-btn <?= $activeTab === 'list' ? 'active' : '' ?>" 
                    onclick="switchTab('list', <?= $selectedProductId ?>)">
                <i class="fas fa-list"></i> Danh Sách (<?= $dataCount ?>)
            </button>
            <button class="import-tab-btn <?= $activeTab === 'import' ? 'active' : '' ?>" 
                    onclick="switchTab('import', <?= $selectedProductId ?>)">
                <i class="fas fa-file-import"></i> Import Excel
            </button>
        </div>

        <!-- Tab Panels -->
        <div class="import-panel <?= $activeTab === 'manual' ? 'active' : '' ?>" id="panel-manual">
            <!-- Manual Entry Form -->
            <?php if ($editDataItem): ?>
            <form method="POST" action="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=manual" class="manual-form">
                <input type="hidden" name="data_action" value="update_row">
                <input type="hidden" name="data_id" value="<?= (int)$editDataItem['id'] ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nhà Cung Cấp</label>
                        <input type="text" name="supplier_name" value="<?= htmlspecialchars($editDataItem['supplier_name'] ?? '') ?>" 
                               placeholder="Tên nhà cung cấp" required>
                    </div>
                    <div class="form-group">
                        <label>Địa Chỉ</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($editDataItem['address'] ?? '') ?>" 
                               placeholder="Địa chỉ">
                    </div>
                    <div class="form-group">
                        <label>WeChat</label>
                        <input type="text" name="wechat_account" value="<?= htmlspecialchars($editDataItem['wechat_account'] ?? '') ?>" 
                               placeholder="Tài khoản WeChat">
                    </div>
                    <div class="form-group">
                        <label>Điện Thoại</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($editDataItem['phone'] ?? '') ?>" 
                               placeholder="Số điện thoại">
                    </div>
                    <div class="form-group full-width">
                        <label>QR WeChat URL</label>
                        <input type="text" name="wechat_qr" value="<?= htmlspecialchars($editDataItem['wechat_qr'] ?? '') ?>" 
                               placeholder="https://...">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Lưu
                        </button>
                        <a href="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=manual" class="btn btn-outline">
                            Hủy
                        </a>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <form method="POST" action="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=manual" class="manual-form">
                <input type="hidden" name="data_action" value="add_manual">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nhà Cung Cấp</label>
                        <input type="text" name="supplier_name" placeholder="Tên nhà cung cấp" required>
                    </div>
                    <div class="form-group">
                        <label>Địa Chỉ</label>
                        <input type="text" name="address" placeholder="Địa chỉ">
                    </div>
                    <div class="form-group">
                        <label>WeChat</label>
                        <input type="text" name="wechat_account" placeholder="Tài khoản WeChat">
                    </div>
                    <div class="form-group">
                        <label>Điện Thoại</label>
                        <input type="text" name="phone" placeholder="Số điện thoại">
                    </div>
                    <div class="form-group full-width">
                        <label>QR WeChat URL</label>
                        <input type="text" name="wechat_qr" placeholder="https://...">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm Dữ Liệu
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <!-- Tab Panel: Data List -->
        <div class="import-panel <?= $activeTab === 'list' ? 'active' : '' ?>" id="panel-list">
            <?php if (!empty($dataList)): ?>
            <div class="section-header">
                <h4><i class="fas fa-list"></i> Danh Sách Dữ Liệu (<?= $dataCount ?>)</h4>
                <form method="POST" action="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=list" 
                      class="delete-all-form" onsubmit="return confirm('Bạn có chắc muốn xóa tất cả dữ liệu?');">
                    <input type="hidden" name="data_action" value="delete_all">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Xóa Tất Cả
                    </button>
                </form>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Nhà Cung Cấp</th>
                        <th>Địa Chỉ</th>
                        <th>WeChat</th>
                        <th>Điện Thoại</th>
                        <th style="width:60px">QR</th>
                        <th style="width:80px">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataList as $index => $item): ?>
                    <tr>
                        <td><?= ($dmPage - 1) * $dmPerPage + $index + 1 ?></td>
                        <td><strong><?= htmlspecialchars($item['supplier_name'] ?? '') ?></strong></td>
                        <td><?= htmlspecialchars($item['address'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['wechat_account'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['phone'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($item['wechat_qr'])): ?>
                            <a href="<?= htmlspecialchars($item['wechat_qr']) ?>" target="_blank" class="btn btn-xs btn-info">
                                <i class="fas fa-qrcode"></i>
                            </a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=manual&dm_action=edit&dm_data_id=<?= (int)$item['id'] ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=list" 
                                      class="delete-form" onsubmit="return confirm('Xóa dòng này?');">
                                    <input type="hidden" name="data_action" value="delete_row">
                                    <input type="hidden" name="data_id" value="<?= (int)$item['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($dataPaginated && $dataPaginated['last_page'] > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $dataPaginated['last_page']; $i++): ?>
                <a href="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=list&dm_page=<?= $i ?>" 
                   class="<?= $i === $dmPage ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="empty-data-state">
                <i class="fas fa-inbox"></i>
                <p>Chưa có dữ liệu. Hãy thêm mới hoặc import từ Excel.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab Panel: Import -->
        <div class="import-panel <?= $activeTab === 'import' ? 'active' : '' ?>" id="panel-import">
            <div class="import-section">
                <div class="import-card">
                    <div class="import-header">
                        <i class="fas fa-file-excel"></i>
                        <h4>Import Dữ Liệu Từ Excel</h4>
                    </div>
                    
                    <div class="import-instructions">
                        <p>Tải lên file Excel (.xlsx) hoặc CSV để import dữ liệu hàng loạt.</p>
                        <div class="format-info">
                            <h5>Định dạng file:</h5>
                            <ul>
                                <li>Chỉ chấp nhận file .xlsx và .csv</li>
                                <li>Cột bắt buộc: <strong>Nhà cung cấp</strong></li>
                                <li>Cột tùy chọn: Địa chỉ, WeChat, Điện thoại, QR</li>
                            </ul>
                        </div>
                    </div>
                    
                    <form id="importForm" class="import-form">
                        <input type="hidden" name="product_id" value="<?= $selectedProductId ?>">
                        <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
                        
                        <div class="file-upload-area" id="dropZone">
                            <input type="file" name="import_file" id="importFile" accept=".xlsx,.csv" style="display:none;">
                            <div class="upload-content" onclick="document.getElementById('importFile').click();">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Kéo thả file vào đây hoặc <span class="browse-btn">duyệt file</span></p>
                                <span class="file-types">.xlsx, .csv (tối đa 5MB)</span>
                            </div>
                        </div>
                        
                        <div class="file-info" id="fileInfo">
                            <i class="fas fa-file-alt"></i>
                            <span class="file-name" id="fileName"></span>
                            <button type="button" class="btn-remove-file" id="removeFile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-import" id="importBtn" disabled>
                            <i class="fas fa-file-import"></i> Import Dữ Liệu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tab, productId) {
    // Update URL without reload
    var url = new URL(window.location.href);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);
    
    // Update tab buttons
    document.querySelectorAll('.import-tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    event.target.closest('.import-tab-btn').classList.add('active');
    
    // Update panels
    document.querySelectorAll('.import-panel').forEach(function(panel) {
        panel.classList.remove('active');
    });
    document.getElementById('panel-' + tab).classList.add('active');
}

// File upload handling
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const importFile = document.getElementById('importFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');
    const importBtn = document.getElementById('importBtn');
    const importForm = document.getElementById('importForm');
    
    // Handle file selection - only trigger once
    if (importFile) {
        importFile.addEventListener('change', function(e) {
            e.stopPropagation();
            if (this.files && this.files[0]) {
                showFileInfo(this.files[0].name);
            }
        });
    }
    
    // Handle drag and drop
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                importFile.files = e.dataTransfer.files;
                showFileInfo(e.dataTransfer.files[0].name);
            }
        });
    }
    
    function showFileInfo(name) {
        const validTypes = ['.xlsx', '.csv'];
        const ext = name.substring(name.lastIndexOf('.')).toLowerCase();
        
        if (validTypes.indexOf(ext) === -1) {
            alert('Chỉ chấp nhận file .xlsx và .csv');
            return;
        }
        
        if (dropZone) dropZone.style.display = 'none';
        if (fileInfo) {
            fileInfo.classList.add('show');
            fileName.textContent = name;
        }
        if (importBtn) importBtn.disabled = false;
    }
    
    // Remove file
    if (removeFile) {
        removeFile.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (importFile) importFile.value = '';
            if (dropZone) dropZone.style.display = 'block';
            if (fileInfo) fileInfo.classList.remove('show');
            if (importBtn) importBtn.disabled = true;
        });
    }
    
    // Handle import form submission via AJAX
    if (importForm && importBtn) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!importFile.files || !importFile.files[0]) {
                alert('Vui lòng chọn file để import');
                return;
            }
            
            const formData = new FormData(importForm);
            
            // Show loading
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang import...';
            
            fetch('?page=admin&module=products&action=data&subaction=import', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reset form
                    importFile.value = '';
                    if (dropZone) dropZone.style.display = 'block';
                    if (fileInfo) fileInfo.classList.remove('show');
                    if (importBtn) importBtn.disabled = true;
                    
                    // Reload page to show updated data
                    window.location.href = '?page=admin&module=products&action=data&id=' + formData.get('product_id') + '&tab=list';
                } else {
                    alert('Lỗi: ' + data.message);
                    if (data.errors) {
                        console.log('Errors:', data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                alert('Lỗi kết nối: ' + error.message);
            })
            .finally(() => {
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="fas fa-file-import"></i> Import Dữ Liệu';
            });
        });
    }
});
</script>
