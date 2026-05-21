<?php
/**
 * Homepage Management - Combined View for Hero Section and Featured Products Section
 */

// Get flash messages
$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';

// Clear flash messages
unset($_SESSION['flash_success']);
unset($_SESSION['flash_error']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Quản lý Trang chủ</h1>
                    <p class="text-muted small mb-0">Quản lý các sections hiển thị trên trang chủ</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Hero Section Card -->
            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-image text-primary me-2"></i>
                        Hero Section
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Banner chính hiển thị ở đầu trang chủ</p>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($heroSections)): ?>
                        <div class="text-center py-5">
                            <img src="https://illustrations.popsy.co/gray/taking-notes.svg" alt="Empty" style="width: 150px;" class="mb-3">
                            <h6 class="text-muted">Chưa có Hero Section</h6>
                            <p class="text-muted small mb-0">Cần tạo Hero Section để hiển thị banner chính</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 text-nowrap" style="width: 60px;">ID</th>
                                        <th class="text-nowrap" style="width: 100px;">Hình ảnh</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Tiêu đề</th>
                                        <th class="text-center text-nowrap" style="width: 100px;">Nút bấm</th>
                                        <th class="text-center text-nowrap" style="width: 120px;">Trạng thái</th>
                                        <th class="text-end pe-4 text-nowrap" style="width: 120px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($heroSections as $section): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $section['id']; ?></td>
                                            <td class="align-middle">
                                                <?php
                                                 $imgUrl = $section['image_url'] ?? '';
                                                 if ($imgUrl) {
                                                     $finalImg = (strpos($imgUrl, 'http') === 0) ? $imgUrl : img_url($imgUrl);
                                                     echo '<img src="'.$finalImg.'" class="rounded shadow-sm" style="width: 80px; height: 50px; object-fit: cover;" onerror="this.src=\'https://via.placeholder.com/80x50?text=No+Image\'">';
                                                } else {
                                                    echo '<div class="rounded bg-light d-flex align-items-center justify-content-center text-muted" style="width: 80px; height: 50px; font-size: 10px;">No Image</div>';
                                                }
                                                ?>
                                            </td>
                                            <td class="align-middle">
                                                <div class="fw-bold text-dark">
                                                    <?php 
                                                    $title = $section['title_main'] ?? '';
                                                    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                                    $title = strip_tags($title);
                                                    $title = trim($title);
                                                    
                                                    if (empty($title)) {
                                                        echo '<span class="text-muted">Không có tiêu đề</span>';
                                                    } else {
                                                        echo mb_strimwidth($title, 0, 50, '...');
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge rounded-pill bg-soft-info text-info">
                                                    <?php echo isset($section['button_count']) ? $section['button_count'] : 0; ?> nút
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if ($section['is_active']): ?>
                                                    <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                                        <i class="fas fa-circle me-1 small"></i> Đang hiện
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                                        <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4 align-middle">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="?page=admin&module=homepage&action=edit-hero&id=<?php echo $section['id']; ?>" 
                                                       class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-icon btn-light-<?php echo $section['is_active'] ? 'warning' : 'success'; ?>"
                                                            onclick="toggleHeroStatus(<?php echo $section['id']; ?>)"
                                                            title="<?php echo $section['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                                        <i class="fas fa-<?php echo $section['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Featured Products Section Card -->
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-star text-warning me-2"></i>
                        Section Sản phẩm Nổi bật
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section sản phẩm nổi bật</p>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($featuredProductsSection)): ?>
                        <div class="text-center py-5">
                            <img src="https://illustrations.popsy.co/gray/product-launch.svg" alt="Empty" style="width: 150px;" class="mb-3">
                            <h6 class="text-muted">Chưa có cấu hình</h6>
                            <p class="text-muted small mb-0">Section sản phẩm nổi bật chưa được cấu hình</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 text-nowrap" style="width: 60px;">ID</th>
                                        <th class="text-nowrap" style="min-width: 300px;">Tiêu đề</th>
                                        <th class="text-center text-nowrap" style="width: 120px;">Trạng thái</th>
                                        <th class="text-end pe-4 text-nowrap" style="width: 120px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $featuredProductsSection['id']; ?></td>
                                        <td class="align-middle">
                                            <div class="text-dark">
                                                <?php 
                                                $title = $featuredProductsSection['title'] ?? '';
                                                $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                                $title = strip_tags($title);
                                                $title = trim($title);
                                                
                                                if (empty($title)) {
                                                    echo '<span class="text-muted">Không có tiêu đề</span>';
                                                } else {
                                                    echo mb_strimwidth($title, 0, 80, '...');
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if ($featuredProductsSection['is_active']): ?>
                                                <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                                    <i class="fas fa-circle me-1 small"></i> Đang hiện
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                                    <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4 align-middle">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="?page=admin&module=homepage&action=edit-featured-products" 
                                                   class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-icon btn-light-<?php echo $featuredProductsSection['is_active'] ? 'warning' : 'success'; ?>"
                                                        onclick="toggleFeaturedProductsStatus(<?php echo $featuredProductsSection['id']; ?>)"
                                                        title="<?php echo $featuredProductsSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                                    <i class="fas fa-<?php echo $featuredProductsSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>

<!-- Latest Products Section -->
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-clock text-info me-2"></i>
                Section Sản phẩm Mới nhất
            </h5>
            <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section sản phẩm mới nhất</p>
        </div>
        <div class="card-body p-0">
            <?php if (empty($latestProductsSection)): ?>
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/product-launch.svg" alt="Empty" style="width: 150px;" class="mb-3">
                    <h6 class="text-muted">Chưa có cấu hình</h6>
                    <p class="text-muted small mb-0">Section sản phẩm mới nhất chưa được cấu hình</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-nowrap" style="width: 60px;">ID</th>
                                <th class="text-nowrap" style="min-width: 300px;">Tiêu đề</th>
                                <th class="text-center text-nowrap" style="width: 120px;">Trạng thái</th>
                                <th class="text-end pe-4 text-nowrap" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $latestProductsSection['id']; ?></td>
                                <td class="align-middle">
                                    <div class="text-dark">
                                        <?php 
                                        $title = $latestProductsSection['title'] ?? '';
                                        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                        $title = strip_tags($title);
                                        $title = trim($title);
                                        
                                        if (empty($title)) {
                                            echo '<span class="text-muted">Không có tiêu đề</span>';
                                        } else {
                                            echo mb_strimwidth($title, 0, 80, '...');
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($latestProductsSection['is_active']): ?>
                                        <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang hiện
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="?page=admin&module=homepage&action=edit-latest-products&id=<?php echo $latestProductsSection['id']; ?>" 
                                           class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-icon btn-light-<?php echo $latestProductsSection['is_active'] ? 'warning' : 'success'; ?>"
                                                onclick="toggleLatestProductsStatus(<?php echo $latestProductsSection['id']; ?>)"
                                                title="<?php echo $latestProductsSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                            <i class="fas fa-<?php echo $latestProductsSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Budget Products Section -->
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-dollar-sign text-success me-2"></i>
                Section Sản phẩm Giá rẻ
            </h5>
            <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section sản phẩm giá rẻ</p>
        </div>
        <div class="card-body p-0">
            <?php if (empty($budgetProductsSection)): ?>
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/product-launch.svg" alt="Empty" style="width: 150px;" class="mb-3">
                    <h6 class="text-muted">Chưa có cấu hình</h6>
                    <p class="text-muted small mb-0">Section sản phẩm giá rẻ chưa được cấu hình</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-nowrap" style="width: 60px;">ID</th>
                                <th class="text-nowrap" style="min-width: 300px;">Tiêu đề</th>
                                <th class="text-center text-nowrap" style="width: 120px;">Trạng thái</th>
                                <th class="text-end pe-4 text-nowrap" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $budgetProductsSection['id']; ?></td>
                                <td class="align-middle">
                                    <div class="text-dark">
                                        <?php 
                                        $title = $budgetProductsSection['title'] ?? '';
                                        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                        $title = strip_tags($title);
                                        $title = trim($title);
                                        
                                        if (empty($title)) {
                                            echo '<span class="text-muted">Không có tiêu đề</span>';
                                        } else {
                                            echo mb_strimwidth($title, 0, 80, '...');
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($budgetProductsSection['is_active']): ?>
                                        <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang hiện
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="?page=admin&module=homepage&action=edit-budget-products&id=<?php echo $budgetProductsSection['id']; ?>" 
                                           class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-icon btn-light-<?php echo $budgetProductsSection['is_active'] ? 'warning' : 'success'; ?>"
                                                onclick="toggleBudgetProductsStatus(<?php echo $budgetProductsSection['id']; ?>)"
                                                title="<?php echo $budgetProductsSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                            <i class="fas fa-<?php echo $budgetProductsSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Sale Products Section -->
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-tag text-danger me-2"></i>
                Section Sản phẩm Giảm giá
            </h5>
            <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section sản phẩm giảm giá</p>
        </div>
        <div class="card-body p-0">
            <?php if (empty($saleProductsSection)): ?>
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/product-launch.svg" alt="Empty" style="width: 150px;" class="mb-3">
                    <h6 class="text-muted">Chưa có cấu hình</h6>
                    <p class="text-muted small mb-0">Section sản phẩm giảm giá chưa được cấu hình</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-nowrap" style="width: 60px;">ID</th>
                                <th class="text-nowrap" style="min-width: 300px;">Tiêu đề</th>
                                <th class="text-center text-nowrap" style="width: 120px;">Trạng thái</th>
                                <th class="text-end pe-4 text-nowrap" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $saleProductsSection['id']; ?></td>
                                <td class="align-middle">
                                    <div class="text-dark">
                                        <?php 
                                        $title = $saleProductsSection['title'] ?? '';
                                        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                        $title = strip_tags($title);
                                        $title = trim($title);
                                        
                                        if (empty($title)) {
                                            echo '<span class="text-muted">Không có tiêu đề</span>';
                                        } else {
                                            echo mb_strimwidth($title, 0, 80, '...');
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($saleProductsSection['is_active']): ?>
                                        <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang hiện
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="?page=admin&module=homepage&action=edit-sale-products&id=<?php echo $saleProductsSection['id']; ?>" 
                                           class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-icon btn-light-<?php echo $saleProductsSection['is_active'] ? 'warning' : 'success'; ?>"
                                                onclick="toggleSaleProductsStatus(<?php echo $saleProductsSection['id']; ?>)"
                                                title="<?php echo $saleProductsSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                            <i class="fas fa-<?php echo $saleProductsSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Featured Categories Section -->
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-folder text-warning me-2"></i>
                Section Danh mục Nổi bật
            </h5>
            <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section danh mục nổi bật</p>
        </div>
        <div class="card-body p-0">
            <?php if (empty($featuredCategoriesSection)): ?>
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/folder.svg" alt="Empty" style="width: 150px;" class="mb-3">
                    <h6 class="text-muted">Chưa có cấu hình</h6>
                    <p class="text-muted small mb-0">Section danh mục nổi bật chưa được cấu hình</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-nowrap" style="width: 60px;">ID</th>
                                <th class="text-nowrap" style="min-width: 300px;">Tiêu đề</th>
                                <th class="text-center text-nowrap" style="width: 120px;">Trạng thái</th>
                                <th class="text-end pe-4 text-nowrap" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $featuredCategoriesSection['id']; ?></td>
                                <td class="align-middle">
                                    <div class="text-dark">
                                        <?php 
                                        $title = $featuredCategoriesSection['title'] ?? '';
                                        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                        $title = strip_tags($title);
                                        $title = trim($title);
                                        
                                        if (empty($title)) {
                                            echo '<span class="text-muted">Không có tiêu đề</span>';
                                        } else {
                                            echo mb_strimwidth($title, 0, 80, '...');
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($featuredCategoriesSection['is_active']): ?>
                                        <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang hiện
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-soft-secondary text-secondary px-3 py-2">
                                            <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 align-middle">
                                    <div class="btn-group" role="group">
                                        <a href="?page=admin&module=homepage&action=edit-featured-categories&id=<?php echo $featuredCategoriesSection['id']; ?>" 
                                           class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-icon btn-light-<?php echo $featuredCategoriesSection['is_active'] ? 'warning' : 'success'; ?>"
                                                onclick="toggleFeaturedCategoriesStatus(<?php echo $featuredCategoriesSection['id']; ?>)"
                                                title="<?php echo $featuredCategoriesSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                            <i class="fas fa-<?php echo $featuredCategoriesSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom Category Sections (Max 5) -->
<div class="col-12">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list text-primary me-2"></i>
                        Section Danh mục Tùy chỉnh (Tối đa 5 sections)
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Tùy biến hiển thị các danh mục sản phẩm nổi bật, giá rẻ, giảm giá, mới nhất</p>
                </div>
                <div>
                    <?php if (count($customCategorySections ?? []) < 5): ?>
                        <a href="?page=admin&module=homepage&action=edit-custom-category" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm Section
                        </a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-secondary" disabled title="Đã đạt giới hạn tối đa 5 section">
                            <i class="fas fa-plus me-1"></i> Thêm Section (Full)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($customCategorySections)): ?>
                <div class="text-center py-5">
                    <h6 class="text-muted">Chưa có cấu hình section tùy chỉnh nào</h6>
                    <p class="text-muted small mb-0">Nhấp vào nút 'Thêm Section' ở góc trên bên phải để bắt đầu.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="table-layout: fixed;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 fw-bold text-muted border-0" style="width: 60px;">ID</th>
                                <th class="fw-bold text-muted border-0" style="width: 220px;">Tiêu đề hiển thị</th>
                                <th class="fw-bold text-muted border-0" style="width: 150px;">Danh mục chọn</th>
                                <th class="fw-bold text-muted border-0" style="width: 120px;">Loại hiển thị</th>
                                <th class="text-center fw-bold text-muted border-0" style="width: 100px;">Trạng thái</th>
                                <th class="text-end pe-4 fw-bold text-muted border-0" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customCategorySections as $sec): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $sec['id']; ?></td>
                                    <td class="align-middle text-truncate fw-semibold" title="<?php echo htmlspecialchars(strip_tags($sec['title'])); ?>">
                                        <?php echo $sec['title']; ?>
                                    </td>
                                    <td class="align-middle text-truncate" title="<?php echo htmlspecialchars($sec['category_name'] ?? 'Chưa xác định'); ?>">
                                        <span class="badge bg-light text-dark border py-1.5 px-2">
                                            <?php echo htmlspecialchars($sec['category_name'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <?php
                                        $typeLabels = [
                                            'featured' => ['label' => 'Nổi bật', 'class' => 'bg-soft-danger text-danger'],
                                            'budget' => ['label' => 'Giá rẻ', 'class' => 'bg-soft-success text-success'],
                                            'sale' => ['label' => 'Giảm giá', 'class' => 'bg-soft-warning text-warning'],
                                            'latest' => ['label' => 'Mới nhất', 'class' => 'bg-soft-info text-info']
                                        ];
                                        $t = $sec['display_type'] ?? 'featured';
                                        $lbl = $typeLabels[$t] ?? ['label' => $t, 'class' => 'bg-soft-secondary text-secondary'];
                                        ?>
                                        <span class="badge rounded-pill <?php echo $lbl['class']; ?> px-2.5 py-1" style="font-size: 11px;">
                                            <?php echo $lbl['label']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($sec['is_active']): ?>
                                            <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                                <i class="fas fa-circle me-1 small"></i> Đang hiện
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-soft-secondary text-muted px-3 py-2">
                                                <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 align-middle">
                                        <div class="btn-group" role="group">
                                            <a href="?page=admin&module=homepage&action=edit-custom-category&id=<?php echo $sec['id']; ?>" 
                                               class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-icon btn-light-<?php echo $sec['is_active'] ? 'warning' : 'success'; ?>"
                                                    onclick="toggleCustomCategoryStatus(<?php echo $sec['id']; ?>)"
                                                    title="<?php echo $sec['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                                <i class="fas fa-<?php echo $sec['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                            <a href="?page=admin&module=homepage&action=delete-custom-category&id=<?php echo $sec['id']; ?>" 
                                               class="btn btn-icon btn-light-danger" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa section này?')" 
                                               title="Xóa">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Featured Brands Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-award text-primary me-2"></i>
                    Section Thương hiệu Nổi bật
                </h5>
                <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section thương hiệu nổi bật</p>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($featuredBrandsSection)): ?>
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/gray/folder.svg" alt="Empty" style="width: 150px;" class="mb-3">
                <h6 class="text-muted">Chưa có cấu hình</h6>
                <p class="text-muted small">Section thương hiệu nổi bật chưa được cấu hình</p>
                <a href="?page=admin&module=homepage&action=create_featured_brands" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tạo cấu hình
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 fw-bold text-muted border-0" style="width: 80px;">ID</th>
                            <th class="fw-bold text-muted border-0">Tiêu đề</th>
                            <th class="text-center fw-bold text-muted border-0" style="width: 120px;">Trạng thái</th>
                            <th class="text-end pe-4 fw-bold text-muted border-0" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $featuredBrandsSection['id']; ?></td>
                            <td class="align-middle">
                                <div class="text-dark">
                                    <?php 
                                    $title = $featuredBrandsSection['title'] ?? '';
                                    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                    $title = strip_tags($title);
                                    $title = trim($title);
                                    echo $title ?: '(Không có tiêu đề)';
                                    ?>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($featuredBrandsSection['is_active']): ?>
                                    <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                        <i class="fas fa-circle me-1 small"></i> Đang hiện
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-soft-secondary text-muted px-3 py-2">
                                        <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 align-middle">
                                <div class="btn-group" role="group">
                                    <a href="?page=admin&module=homepage&action=edit_featured_brands" 
                                       class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-icon btn-light-<?php echo $featuredBrandsSection['is_active'] ? 'warning' : 'success'; ?>"
                                            onclick="toggleFeaturedBrandsStatus(<?php echo $featuredBrandsSection['id']; ?>)"
                                            title="<?php echo $featuredBrandsSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                        <i class="fas fa-<?php echo $featuredBrandsSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Latest News Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-newspaper text-info me-2"></i>
                    Section Tin tức Mới nhất
                </h5>
                <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề và hiển thị section tin tức mới nhất</p>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($latestNewsSection)): ?>
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/gray/folder.svg" alt="Empty" style="width: 150px;" class="mb-3">
                <h6 class="text-muted">Chưa có cấu hình</h6>
                <p class="text-muted small">Section tin tức mới nhất chưa được cấu hình</p>
                <a href="?page=admin&module=homepage&action=edit_latest_news" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tạo cấu hình
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 fw-bold text-muted border-0" style="width: 80px;">ID</th>
                            <th class="fw-bold text-muted border-0">Tiêu đề</th>
                            <th class="text-center fw-bold text-muted border-0" style="width: 120px;">Trạng thái</th>
                            <th class="text-end pe-4 fw-bold text-muted border-0" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $latestNewsSection['id']; ?></td>
                            <td class="align-middle">
                                <div class="text-dark">
                                    <?php 
                                    $title = $latestNewsSection['title'] ?? '';
                                    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                    $title = strip_tags($title);
                                    $title = trim($title);
                                    echo $title ?: '(Không có tiêu đề)';
                                    ?>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($latestNewsSection['is_active']): ?>
                                    <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                        <i class="fas fa-circle me-1 small"></i> Đang hiện
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-soft-secondary text-muted px-3 py-2">
                                        <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 align-middle">
                                <div class="btn-group" role="group">
                                    <a href="?page=admin&module=homepage&action=edit_latest_news" 
                                       class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-icon btn-light-<?php echo $latestNewsSection['is_active'] ? 'warning' : 'success'; ?>"
                                            onclick="toggleLatestNewsStatus(<?php echo $latestNewsSection['id']; ?>)"
                                            title="<?php echo $latestNewsSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                        <i class="fas fa-<?php echo $latestNewsSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Why Choose Us Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-question-circle text-primary me-2"></i>
                    Section Tại sao chọn ThuongLo
                </h5>
                <p class="text-muted small mb-0 mt-1">Quản lý tiêu đề, trạng thái và các khối thông tin lý do chọn ThuongLo</p>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($whyChooseSection)): ?>
            <div class="text-center py-5">
                <img src="https://illustrations.popsy.co/gray/folder.svg" alt="Empty" style="width: 150px;" class="mb-3">
                <h6 class="text-muted">Chưa có cấu hình</h6>
                <p class="text-muted small">Section "Tại sao chọn ThuongLo" chưa được cấu hình</p>
                <a href="?page=admin&module=homepage&action=edit_why_choose" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tạo cấu hình
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 fw-bold text-muted border-0" style="width: 80px;">ID</th>
                            <th class="fw-bold text-muted border-0">Tiêu đề</th>
                            <th class="text-center fw-bold text-muted border-0" style="width: 120px;">Trạng thái</th>
                            <th class="text-end pe-4 fw-bold text-muted border-0" style="width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold text-muted align-middle">#<?php echo $whyChooseSection['id']; ?></td>
                            <td class="align-middle">
                                <div class="text-dark">
                                    <?php 
                                    $title = $whyChooseSection['title'] ?? '';
                                    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                                    $title = strip_tags($title);
                                    $title = trim($title);
                                    echo $title ?: '(Không có tiêu đề)';
                                    ?>
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <?php if ($whyChooseSection['is_active']): ?>
                                    <span class="badge rounded-pill bg-soft-success text-success px-3 py-2">
                                        <i class="fas fa-circle me-1 small"></i> Đang hiện
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-soft-secondary text-muted px-3 py-2">
                                        <i class="fas fa-circle me-1 small"></i> Đang ẩn
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4 align-middle">
                                <div class="btn-group" role="group">
                                    <a href="?page=admin&module=homepage&action=edit_why_choose" 
                                       class="btn btn-icon btn-light-primary" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-icon btn-light-<?php echo $whyChooseSection['is_active'] ? 'warning' : 'success'; ?>"
                                            onclick="toggleWhyChooseStatus(<?php echo $whyChooseSection['id']; ?>)"
                                            title="<?php echo $whyChooseSection['is_active'] ? 'Tạm ẩn' : 'Hiển thị'; ?>">
                                        <i class="fas fa-<?php echo $whyChooseSection['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
</div>



<script>
function toggleCustomCategoryStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section này?')) {
        fetch('?page=admin&module=homepage&action=toggle-custom-category-status', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleHeroStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Hero Section này?')) {
        fetch('?page=admin&module=homepage&action=toggle-hero-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleFeaturedProductsStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Sản phẩm Nổi bật?')) {
        fetch('?page=admin&module=homepage&action=toggle-featured-products-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleLatestProductsStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Sản phẩm Mới nhất?')) {
        fetch('?page=admin&module=homepage&action=toggle-latest-products-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleBudgetProductsStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Sản phẩm Giá rẻ?')) {
        fetch('?page=admin&module=homepage&action=toggle-budget-products-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleSaleProductsStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Sản phẩm Giảm giá?')) {
        fetch('?page=admin&module=homepage&action=toggle-sale-products-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleFeaturedCategoriesStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Danh mục Nổi bật?')) {
        fetch('?page=admin&module=homepage&action=toggle-featured-categories-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleFeaturedBrandsStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Thương hiệu Nổi bật?')) {
        fetch('?page=admin&module=homepage&action=toggle-featured-brands-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleLatestNewsStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Tin tức Mới nhất?')) {
        fetch('?page=admin&module=homepage&action=toggle-latest-news-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}

function toggleWhyChooseStatus(id) {
    if (confirm('Bạn có muốn thay đổi trạng thái hiển thị của Section Tại sao chọn ThuongLo?')) {
        fetch('?page=admin&module=homepage&action=toggle-why-choose-status', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) window.location.reload();
            else alert(d.message);
        });
    }
}
</script>

<style>
.bg-soft-success { background-color: #dcfce7; }
.bg-soft-info { background-color: #e0f2fe; }
.bg-soft-secondary { background-color: #f3f4f6; }
.text-success { color: #16a34a !important; }
.text-info { color: #0284c7 !important; }
.text-secondary { color: #4b5563 !important; }

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-light-primary { color: #356DF1; background-color: #eff6ff; border: none; }
.btn-light-primary:hover { background-color: #356DF1; color: white; }

.btn-light-warning { color: #d97706; background-color: #fffbeb; border: none; }
.btn-light-warning:hover { background-color: #d97706; color: white; }

.btn-light-success { color: #16a34a; background-color: #f0fdf4; border: none; }
.btn-light-success:hover { background-color: #16a34a; color: white; }

.btn-light-danger { color: #dc2626; background-color: #fef2f2; border: none; }
.btn-light-danger:hover { background-color: #dc2626; color: white; }

.table th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    color: #6b7280;
    vertical-align: middle !important;
}

.table td {
    vertical-align: middle !important;
}

.table {
    table-layout: fixed;
}

.card {
    border-radius: 12px;
}

.table-responsive {
    overflow-x: auto;
}

.text-center {
    text-align: center !important;
}

.text-end {
    text-align: right !important;
}

.ps-4 {
    padding-left: 1.5rem !important;
}

.pe-4 {
    padding-right: 1.5rem !important;
}
</style>
