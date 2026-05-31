<?php
/**
 * Admin Products Data Management Page
 * Flow: Product List -> Select Product -> Data Management
 */

// Get parameters from URL
$search = $_GET['search'] ?? '';
$categoryFilter = (int)($_GET['category'] ?? 0);
$selectedProductId = (int)($_GET['id'] ?? 0); // Using 'id' parameter for selected product
$page = max(1, (int)($_GET['p'] ?? $_GET['page'] ?? 1));
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
                    // Handle file uploads
                    $storeImagePath = '';
                    $wechatQrPath = '';
                    
                    // Store image upload
                    if (isset($_FILES['store_image_file']) && $_FILES['store_image_file']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'assets/images/store/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $ext = strtolower(pathinfo($_FILES['store_image_file']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg','jpeg','png','gif','webp'];
                        if (in_array($ext, $allowed)) {
                            $filename = 'store_' . time() . '_' . uniqid() . '.' . $ext;
                            $dest = $upload_dir . $filename;
                            if (move_uploaded_file($_FILES['store_image_file']['tmp_name'], $dest)) {
                                $storeImagePath = $dest;
                            }
                        }
                    } elseif (!empty($_POST['store_image_url'])) {
                        $storeImagePath = trim($_POST['store_image_url']);
                    }
                    
                    // WeChat QR upload
                    if (isset($_FILES['wechat_qr_file']) && $_FILES['wechat_qr_file']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'assets/images/qr/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $ext = strtolower(pathinfo($_FILES['wechat_qr_file']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg','jpeg','png','gif','webp'];
                        if (in_array($ext, $allowed)) {
                            $filename = 'qr_' . time() . '_' . uniqid() . '.' . $ext;
                            $dest = $upload_dir . $filename;
                            if (move_uploaded_file($_FILES['wechat_qr_file']['tmp_name'], $dest)) {
                                $wechatQrPath = $dest;
                            }
                        }
                    } elseif (!empty($_POST['wechat_qr'])) {
                        $wechatQrPath = trim($_POST['wechat_qr']);
                    }
                    
                    $data = [
                        'product_id' => $selectedProductId,
                        'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                        'address' => trim($_POST['address'] ?? ''),
                        'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                        'phone' => trim($_POST['phone'] ?? ''),
                        'wechat_qr' => $wechatQrPath,
                        'store_image' => $storeImagePath,
                        'style_classification' => trim($_POST['style_classification'] ?? '')
                    ];
                    $productDataModel->create($data);
                    $message = 'Đã thêm dữ liệu thành công!';
                    $messageType = 'success';
                    break;
                    
                case 'update_row':
                    if (!empty($_POST['data_id'])) {
                        $dataId = (int)$_POST['data_id'];
                        
                        // Handle file uploads
                        $storeImagePath = '';
                        $wechatQrPath = '';
                        
                        // Store image upload
                        if (isset($_FILES['store_image_file']) && $_FILES['store_image_file']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = 'assets/images/store/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            $ext = strtolower(pathinfo($_FILES['store_image_file']['name'], PATHINFO_EXTENSION));
                            $allowed = ['jpg','jpeg','png','gif','webp'];
                            if (in_array($ext, $allowed)) {
                                $filename = 'store_' . time() . '_' . uniqid() . '.' . $ext;
                                $dest = $upload_dir . $filename;
                                if (move_uploaded_file($_FILES['store_image_file']['tmp_name'], $dest)) {
                                    $storeImagePath = $dest;
                                }
                            }
                        } elseif (!empty($_POST['store_image_url'])) {
                            $storeImagePath = trim($_POST['store_image_url']);
                        }
                        
                        // WeChat QR upload
                        if (isset($_FILES['wechat_qr_file']) && $_FILES['wechat_qr_file']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = 'assets/images/qr/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            $ext = strtolower(pathinfo($_FILES['wechat_qr_file']['name'], PATHINFO_EXTENSION));
                            $allowed = ['jpg','jpeg','png','gif','webp'];
                            if (in_array($ext, $allowed)) {
                                $filename = 'qr_' . time() . '_' . uniqid() . '.' . $ext;
                                $dest = $upload_dir . $filename;
                                if (move_uploaded_file($_FILES['wechat_qr_file']['tmp_name'], $dest)) {
                                    $wechatQrPath = $dest;
                                }
                            }
                        } elseif (!empty($_POST['wechat_qr'])) {
                            $wechatQrPath = trim($_POST['wechat_qr']);
                        }
                        
                        $data = [
                            'supplier_name' => trim($_POST['supplier_name'] ?? ''),
                            'address' => trim($_POST['address'] ?? ''),
                            'wechat_account' => trim($_POST['wechat_account'] ?? ''),
                            'phone' => trim($_POST['phone'] ?? ''),
                            'wechat_qr' => $wechatQrPath ?: trim($_POST['wechat_qr'] ?? ''),
                            'store_image' => $storeImagePath ?: trim($_POST['store_image_url'] ?? ''),
                            'style_classification' => trim($_POST['style_classification'] ?? '')
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
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=admin&module=products&action=data&p=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>" 
                       class="pagination-btn">
                        <i class="fas fa-chevron-left"></i>
                        Trước
                    </a>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($totalPages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?page=admin&module=products&action=data&p=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>" 
                       class="pagination-number <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=admin&module=products&action=data&p=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $categoryFilter ? '&category=' . $categoryFilter : '' ?>" 
                       class="pagination-btn">
                        Sau
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-info">
                Trang <?= $page ?> / <?= $totalPages ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- VIEW 2: Data Management for Selected Product -->
    <div class="section data-panel">

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
            <form method="POST" action="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=manual" class="manual-form" enctype="multipart/form-data">
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
                    <div class="form-group">
                        <label>Phân Loại Phong Cách</label>
                        <input type="text" name="style_classification" value="<?= htmlspecialchars($editDataItem['style_classification'] ?? '') ?>" 
                               placeholder="Ví dụ: Hàn Quốc, Nhật Bản, Âu Mỹ...">
                    </div>
                    <div class="form-group full-width">
                        <label>Ảnh Cửa Hàng</label>
                        <div style="margin-bottom:8px;">
                            <div style="border:2px dashed #d1d5db;border-radius:6px;padding:16px;text-align:center;cursor:pointer;transition:border-color 0.3s;" 
                                 onclick="document.getElementById('store_image_file').click()" id="storeImageUploadZone">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#9ca3af;margin-bottom:8px;display:block;"></i>
                                <p style="margin:0;color:#6b7280;font-size:14px;">Nhấp để chọn ảnh cửa hàng</p>
                                <p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
                            </div>
                            <input type="file" id="store_image_file" name="store_image_file" accept="image/*" style="display:none;"
                                   onchange="previewStoreImage(this)">
                            <div id="storeImagePreview" style="margin-top:8px;display:none;">
                                <img id="storePreviewImg" src="" alt="Store Preview" style="max-width:200px;max-height:150px;border-radius:6px;object-fit:cover;border:1px solid #ddd;">
                                <p style="margin:4px 0 0;font-size:12px;color:#10B981;"><i class="fas fa-check-circle"></i> Ảnh cửa hàng đã chọn</p>
                            </div>
                        </div>
                        <input type="text" id="store_image_url" name="store_image_url" 
                               value="<?= htmlspecialchars($editDataItem['store_image'] ?? '') ?>" 
                               placeholder="Hoặc nhập URL ảnh cửa hàng: https://...">
                        <small>Nếu upload ảnh thì URL này sẽ bị bỏ qua</small>
                    </div>
                    <div class="form-group full-width">
                        <label>QR WeChat URL</label>
                        <div style="margin-bottom:8px;">
                            <div style="border:2px dashed #d1d5db;border-radius:6px;padding:16px;text-align:center;cursor:pointer;transition:border-color 0.3s;" 
                                 onclick="document.getElementById('wechat_qr_file').click()" id="qrImageUploadZone">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#9ca3af;margin-bottom:8px;display:block;"></i>
                                <p style="margin:0;color:#6b7280;font-size:14px;">Nhấp để chọn ảnh QR WeChat</p>
                                <p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
                            </div>
                            <input type="file" id="wechat_qr_file" name="wechat_qr_file" accept="image/*" style="display:none;"
                                   onchange="previewQrImage(this)">
                            <div id="qrImagePreview" style="margin-top:8px;display:none;">
                                <img id="qrPreviewImg" src="" alt="QR Preview" style="max-width:200px;max-height:150px;border-radius:6px;object-fit:cover;border:1px solid #ddd;">
                                <p style="margin:4px 0 0;font-size:12px;color:#10B981;"><i class="fas fa-check-circle"></i> Ảnh QR đã chọn</p>
                            </div>
                        </div>
                        <input type="url" id="wechat_qr" name="wechat_qr" 
                               value="<?= htmlspecialchars($editDataItem['wechat_qr'] ?? '') ?>" 
                               placeholder="Hoặc nhập URL QR WeChat: https://...">
                        <small>Nếu upload ảnh thì URL này sẽ bị bỏ qua</small>
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
            <form method="POST" action="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=manual" class="manual-form" enctype="multipart/form-data">
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
                    <div class="form-group">
                        <label>Phân Loại Phong Cách</label>
                        <input type="text" name="style_classification" placeholder="Ví dụ: Hàn Quốc, Nhật Bản, Âu Mỹ...">
                    </div>
                    <div class="form-group full-width">
                        <label>Ảnh Cửa Hàng</label>
                        <div style="margin-bottom:8px;">
                            <div style="border:2px dashed #d1d5db;border-radius:6px;padding:16px;text-align:center;cursor:pointer;transition:border-color 0.3s;" 
                                 onclick="document.getElementById('store_image_file_add').click()" id="storeImageUploadZoneAdd">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#9ca3af;margin-bottom:8px;display:block;"></i>
                                <p style="margin:0;color:#6b7280;font-size:14px;">Nhấp để chọn ảnh cửa hàng</p>
                                <p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
                            </div>
                            <input type="file" id="store_image_file_add" name="store_image_file" accept="image/*" style="display:none;"
                                   onchange="previewStoreImageAdd(this)">
                            <div id="storeImagePreviewAdd" style="margin-top:8px;display:none;">
                                <img id="storePreviewImgAdd" src="" alt="Store Preview" style="max-width:200px;max-height:150px;border-radius:6px;object-fit:cover;border:1px solid #ddd;">
                                <p style="margin:4px 0 0;font-size:12px;color:#10B981;"><i class="fas fa-check-circle"></i> Ảnh cửa hàng đã chọn</p>
                            </div>
                        </div>
                        <input type="text" id="store_image_url_add" name="store_image_url" 
                               placeholder="Hoặc nhập URL ảnh cửa hàng: https://...">
                        <small>Nếu upload ảnh thì URL này sẽ bị bỏ qua</small>
                    </div>
                    <div class="form-group full-width">
                        <label>QR WeChat URL</label>
                        <div style="margin-bottom:8px;">
                            <div style="border:2px dashed #d1d5db;border-radius:6px;padding:16px;text-align:center;cursor:pointer;transition:border-color 0.3s;" 
                                 onclick="document.getElementById('wechat_qr_file_add').click()" id="qrImageUploadZoneAdd">
                                <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:#9ca3af;margin-bottom:8px;display:block;"></i>
                                <p style="margin:0;color:#6b7280;font-size:14px;">Nhấp để chọn ảnh QR WeChat</p>
                                <p style="margin:4px 0 0;font-size:12px;color:#9ca3af;">JPG, PNG, GIF, WebP — Tối đa 5MB</p>
                            </div>
                            <input type="file" id="wechat_qr_file_add" name="wechat_qr_file" accept="image/*" style="display:none;"
                                   onchange="previewQrImageAdd(this)">
                            <div id="qrImagePreviewAdd" style="margin-top:8px;display:none;">
                                <img id="qrPreviewImgAdd" src="" alt="QR Preview" style="max-width:200px;max-height:150px;border-radius:6px;object-fit:cover;border:1px solid #ddd;">
                                <p style="margin:4px 0 0;font-size:12px;color:#10B981;"><i class="fas fa-check-circle"></i> Ảnh QR đã chọn</p>
                            </div>
                        </div>
                        <input type="url" id="wechat_qr_add" name="wechat_qr" 
                               placeholder="Hoặc nhập URL QR WeChat: https://...">
                        <small>Nếu upload ảnh thì URL này sẽ bị bỏ qua</small>
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
                        <th style="width:40px">STT</th>
                        <th>Tên nhà cung cấp</th>
                        <th>Địa chỉ</th>
                        <th>WeChat</th>
                        <th>Điện thoại</th>
                        <th>Phân loại phong cách</th>
                        <th>Ảnh cửa hàng</th>
                        <th>QR WeChat</th>
                        <th style="width:80px">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $startIndex = ($dataPaginated['current_page'] - 1) * $dataPaginated['per_page'];
                    foreach ($dataList as $index => $item): 
                    ?>
                    <tr>
                        <td><?= $startIndex + $index + 1; ?></td>
                        <td><strong><?= htmlspecialchars($item['supplier_name'] ?? '') ?></strong></td>
                        <td><?= htmlspecialchars($item['address'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['wechat_account'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($item['style_classification'] ?? '') ?></td>
                        <td class="store-image-cell">
                            <?php if (!empty($item['store_image'])): ?>
                            <span class="store-image-trigger" onclick="openQrModal('<?= htmlspecialchars($item['store_image']); ?>')" title="Xem ảnh cửa hàng">
                                <i class="fas fa-store store-image-icon"></i>
                            </span>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="qr-cell">
                            <?php if (!empty($item['wechat_qr'])): ?>
                            <span class="qr-trigger" onclick="openQrModal('<?= htmlspecialchars($item['wechat_qr']); ?>')" title="Xem QR">
                                <i class="fas fa-qrcode qr-icon" title="Xem QR"></i>
                            </span>
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
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($dmPage > 1): ?>
                        <a href="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=list&dm_page=<?= $dmPage - 1 ?>" 
                           class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                            Trước
                        </a>
                    <?php endif; ?>

                    <?php
                    $total_dm_pages = $dataPaginated['last_page'];
                    $start_dm_page = max(1, $dmPage - 2);
                    $end_dm_page = min($total_dm_pages, $dmPage + 2);
                    
                    for ($i = $start_dm_page; $i <= $end_dm_page; $i++): ?>
                        <a href="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=list&dm_page=<?= $i ?>" 
                           class="pagination-number <?= $i === $dmPage ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($dmPage < $total_dm_pages): ?>
                        <a href="?page=admin&module=products&action=data&id=<?= $selectedProductId ?>&tab=list&dm_page=<?= $dmPage + 1 ?>" 
                           class="pagination-btn">
                            Sau
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="pagination-info">
                    Trang <?= $dmPage ?> / <?= $total_dm_pages ?>
                </div>
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
                                <li>Cột tùy chọn: Địa chỉ, WeChat, Điện thoại, Phân loại phong cách, Ảnh cửa hàng (URL), QR WeChat (URL)</li>
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

// Image preview functions for edit form
function previewStoreImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('storePreviewImg').src = e.target.result;
            document.getElementById('storeImagePreview').style.display = 'block';
            // Clear URL input when file is selected
            document.getElementById('store_image_url').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewQrImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('qrPreviewImg').src = e.target.result;
            document.getElementById('qrImagePreview').style.display = 'block';
            // Clear URL input when file is selected
            document.getElementById('wechat_qr').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Image preview functions for add form
function previewStoreImageAdd(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('storePreviewImgAdd').src = e.target.result;
            document.getElementById('storeImagePreviewAdd').style.display = 'block';
            // Clear URL input when file is selected
            document.getElementById('store_image_url_add').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewQrImageAdd(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('qrPreviewImgAdd').src = e.target.result;
            document.getElementById('qrImagePreviewAdd').style.display = 'block';
            // Clear URL input when file is selected
            document.getElementById('wechat_qr_add').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Drag and drop support for edit form
var storeUploadZone = document.getElementById('storeImageUploadZone');
if (storeUploadZone) {
    storeUploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3B82F6';
        this.style.background = '#EFF6FF';
    });
    storeUploadZone.addEventListener('dragleave', function() {
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
    });
    storeUploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('store_image_file').files = files;
            previewStoreImage(document.getElementById('store_image_file'));
        }
    });
}

var qrUploadZone = document.getElementById('qrImageUploadZone');
if (qrUploadZone) {
    qrUploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3B82F6';
        this.style.background = '#EFF6FF';
    });
    qrUploadZone.addEventListener('dragleave', function() {
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
    });
    qrUploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('wechat_qr_file').files = files;
            previewQrImage(document.getElementById('wechat_qr_file'));
        }
    });
}

// Drag and drop support for add form
var storeUploadZoneAdd = document.getElementById('storeImageUploadZoneAdd');
if (storeUploadZoneAdd) {
    storeUploadZoneAdd.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3B82F6';
        this.style.background = '#EFF6FF';
    });
    storeUploadZoneAdd.addEventListener('dragleave', function() {
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
    });
    storeUploadZoneAdd.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('store_image_file_add').files = files;
            previewStoreImageAdd(document.getElementById('store_image_file_add'));
        }
    });
}

var qrUploadZoneAdd = document.getElementById('qrImageUploadZoneAdd');
if (qrUploadZoneAdd) {
    qrUploadZoneAdd.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3B82F6';
        this.style.background = '#EFF6FF';
    });
    qrUploadZoneAdd.addEventListener('dragleave', function() {
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
    });
    qrUploadZoneAdd.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.background = '';
        var files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('wechat_qr_file_add').files = files;
            previewQrImageAdd(document.getElementById('wechat_qr_file_add'));
        }
    });
}
</script>

<!-- QR Modal Lightbox -->
<div id="qrModal" class="qr-modal" onclick="closeQrModal(event)">
    <div class="qr-modal-content" onclick="event.stopPropagation()">
        <span class="qr-modal-close" onclick="closeQrModal()">&times;</span>
        <img id="qrModalImage" src="" alt="Image">
    </div>
</div>

<style>
/* Modal styles for admin page */
.qr-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    cursor: pointer;
}

.qr-modal-content {
    position: relative;
    margin: auto;
    padding: 20px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.qr-modal-content img {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
}

.qr-modal-close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.qr-modal-close:hover {
    color: #bbb;
}

/* Store image and QR cell styles */
.store-image-cell, .qr-cell {
    text-align: center;
    vertical-align: middle;
}

.store-image-trigger, .qr-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.store-image-trigger {
    background-color: #f3f4f6;
    color: #6b7280;
}

.store-image-trigger:hover {
    background-color: #e5e7eb;
    color: #374151;
}

.store-image-icon {
    font-size: 14px;
}

.qr-trigger {
    background-color: #f0fdf4;
    border: 1px solid #dcfce7;
}

.qr-trigger:hover {
    background-color: #dcfce7;
    border-color: #bbf7d0;
}

.qr-icon {
    font-size: 16px;
    color: #16a34a;
}
</style>

<script>
// Modal functions for admin page
let qrModalTimer = null;

function openQrModal(imageSrc) {
    const modal = document.getElementById('qrModal');
    const modalImg = document.getElementById('qrModalImage');
    modalImg.src = imageSrc;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Auto-close after 5 seconds
    clearTimeout(qrModalTimer);
    qrModalTimer = setTimeout(() => {
        closeQrModal();
    }, 5000);
}

function closeQrModal(event) {
    // If event is provided, only close if clicking the overlay (not the image)
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('qrModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';

    // Clear src after animation
    setTimeout(() => {
        document.getElementById('qrModalImage').src = '';
    }, 300);
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQrModal();
    }
});
</script>
