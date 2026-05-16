<?php
/**
 * Filter Configuration Page - Cấu hình bộ lọc sản phẩm
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Cấu Hình Filter Sản Phẩm';

// Get real data from FilterConfigService with error handling
try {
    require_once __DIR__ . '/../../../services/FilterConfigService.php';
    $filterService = new FilterConfigService();

    // Get real data
    $categories_data = $filterService->getCategoriesForFilter();
    $brands_data = $filterService->getBrandsForFilter();
    $price_ranges_data = $filterService->getPriceRangesForFilter();

    // Get current filter configuration from database
    $config_result = $filterService->getFilterConfig();
    $filter_config = $config_result['success'] ? $config_result['data'] : [];
    
} catch (Exception $e) {
    // Fallback data if service fails
    error_log('Filter config error: ' . $e->getMessage());
    $categories_data = [];
    $brands_data = [];
    $price_ranges_data = [];
    $filter_config = [];
}

// Make sure data is arrays
$categories_data = is_array($categories_data) ? $categories_data : [];
$brands_data = is_array($brands_data) ? $brands_data : [];
$price_ranges_data = is_array($price_ranges_data) ? $price_ranges_data : [];
$filter_config = is_array($filter_config) ? $filter_config : [];

// Recursive function to display categories at all levels
function displayCategoryChildren($children, $level = 1) {
    foreach ($children as $child) {
        ?>
        <div class="criteria-item sub-item level-<?= $level ?>" data-id="<?= $child['id'] ?? '' ?>" data-parent="<?= $child['parent_id'] ?? 0 ?>">
            <div class="item-content">
                <div class="drag-handle">
                    <i class="fas fa-grip-lines"></i>
                </div>
                <div class="item-info">
                    <span class="item-name"><?= htmlspecialchars($child['name'] ?? '') ?></span>
                    <span class="item-count">(<?= $child['count'] ?? 0 ?>)</span>
                </div>
                <div class="item-controls">
                    <?php if (!empty($child['children'])): ?>
                        <button type="button" class="toggle-children" onclick="toggleChildren(this)">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    <?php endif; ?>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <?php if (!empty($child['children'])): ?>
                <div class="sub-items level-<?= $level + 1 ?>" style="display: none;">
                    <?php displayCategoryChildren($child['children'], $level + 1); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>

<!-- Filter Configuration Page -->
<div class="filter-config-page">

<!-- Filter Configuration Header -->
<div class="filter-config-header">
    <div class="header-info">
        <h2 class="section-title">
            <i class="fas fa-sliders-h"></i>
            Cấu Hình Bộ Lọc Sản Phẩm
        </h2>
        <p class="section-description">
            Kéo thả để sắp xếp thứ tự các tiêu chí lọc và các mục con bên trong.
            Các mục con chỉ có thể được sắp xếp trong cùng cấp với nhau.
        </p>
    </div>
    <div class="header-actions">
        <button type="button" class="btn btn-primary" onclick="saveFilterConfig()">
            <i class="fas fa-save"></i>
            Lưu Cấu Hình
        </button>
        <button type="button" class="btn btn-secondary" onclick="resetFilterConfig()">
            <i class="fas fa-undo"></i>
            Reset Mặc Định
        </button>
    </div>
</div>

<!-- Filter Criteria Container -->
<div class="filter-criteria-container" id="filterCriteriaContainer">
    
    <!-- Categories Filter -->
    <div class="filter-criteria" data-criteria="categories" data-order="1">
        <div class="criteria-header">
            <div class="criteria-title">
                <i class="fas fa-folder"></i>
                <span>Danh Mục</span>
                <span class="item-count">(<?= count($categories_data) ?>)</span>
            </div>
            <div class="criteria-controls">
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
                <div class="drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
        </div>
        <div class="criteria-items">
            <?php if (!empty($categories_data)): ?>
                <?php foreach ($categories_data as $category): ?>
                    <div class="criteria-item" data-id="<?= $category['id'] ?? '' ?>" data-parent="<?= $category['parent_id'] ?? 0 ?>">
                        <div class="item-content">
                            <div class="drag-handle">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="item-info">
                                <span class="item-name"><?= htmlspecialchars($category['name'] ?? '') ?></span>
                                <span class="item-count">(<?= $category['count'] ?? 0 ?>)</span>
                            </div>
                            <div class="item-controls">
                                <?php if (!empty($category['children'])): ?>
                                    <button type="button" class="toggle-children" onclick="toggleChildren(this)">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                <?php endif; ?>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <?php if (!empty($category['children'])): ?>
                            <div class="sub-items" style="display: none;">
                                <?php displayCategoryChildren($category['children']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    <p>Không có danh mục nào. Vui lòng thêm danh mục trước.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Brands Filter -->
    <div class="filter-criteria" data-criteria="brands" data-order="2">
        <div class="criteria-header">
            <div class="criteria-title">
                <i class="fas fa-tag"></i>
                <span>Thương Hiệu</span>
                <span class="item-count">(<?= count($brands_data) ?>)</span>
            </div>
            <div class="criteria-controls">
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
                <div class="drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
        </div>
        <div class="criteria-items">
            <?php if (!empty($brands_data)): ?>
                <?php foreach ($brands_data as $brand): ?>
                    <div class="criteria-item" data-id="<?= $brand['id'] ?? '' ?>" data-parent="0">
                        <div class="item-content">
                            <div class="drag-handle">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="item-info">
                                <span class="item-name"><?= htmlspecialchars($brand['name'] ?? '') ?></span>
                                <span class="item-count">(<?= $brand['count'] ?? 0 ?>)</span>
                            </div>
                            <div class="item-controls">
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    <p>Không có thương hiệu nào. Vui lòng thêm thương hiệu trước.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Price Ranges Filter -->
    <div class="filter-criteria" data-criteria="price_ranges" data-order="3">
        <div class="criteria-header">
            <div class="criteria-title">
                <i class="fas fa-dollar-sign"></i>
                <span>Khoảng Giá</span>
                <span class="item-count">(<?= count($price_ranges_data) ?>)</span>
            </div>
            <div class="criteria-controls">
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
                <div class="drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
            </div>
        </div>
        <div class="criteria-items">
            <?php if (!empty($price_ranges_data)): ?>
                <?php foreach ($price_ranges_data as $range): ?>
                    <div class="criteria-item" data-id="<?= $range['id'] ?? '' ?>" data-parent="0">
                        <div class="item-content">
                            <div class="drag-handle">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="item-info">
                                <span class="item-name"><?= htmlspecialchars($range['name'] ?? '') ?></span>
                            </div>
                            <div class="item-controls">
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-items">
                    <p>Không có khoảng giá nào. Vui lòng thêm khoảng giá trước.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

</div>

<!-- Success Message -->
<div id="successMessage" class="success-message" style="display: none;">
    <i class="fas fa-check-circle"></i>
    <span>Cấu hình đã được lưu thành công!</span>
</div>

<style>
/* Filter Config Page - Match admin products styling */
.filter-config-page {
    padding: 24px;
}

.filter-config-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding: 20px 24px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.header-info h2 {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-info h2 i {
    color: #356DF1;
}

.header-info p {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-criteria-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.filter-criteria {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.criteria-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.criteria-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    font-size: 16px;
    color: #111827;
}

.criteria-title i {
    color: #356DF1;
}

.item-count {
    background: #dbeafe;
    color: #1e40af;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.criteria-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.toggle-children {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
    font-size: 12px;
}

.toggle-children:hover {
    background: #f3f4f6;
    color: #356DF1;
}

.toggle-children.expanded {
    color: #356DF1;
}

.drag-handle {
    cursor: grab;
    color: #9ca3af;
    transition: color 0.2s;
}

.drag-handle:hover {
    color: #356DF1;
}

.drag-handle:active {
    cursor: grabbing;
}

.criteria-items {
    padding: 20px;
}

.criteria-item {
    margin-bottom: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    transition: all 0.2s;
}

.criteria-item:hover {
    border-color: #356DF1;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.criteria-item.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.filter-criteria.dragging {
    opacity: 0.5;
    transform: scale(0.98);
    cursor: grabbing;
}

.filter-criteria.drag-over {
    background: #f0f9ff;
    border-color: #3b82f6;
    transform: scale(1.02);
}

.criteria-header.drag-over {
    background: #eff6ff;
    border-color: #3b82f6;
}

.item-content {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    gap: 12px;
}

.item-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-name {
    font-weight: 500;
    color: #374151;
}

.item-count {
    background: #f3f4f6;
    color: #6b7280;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.sub-items {
    margin-left: 40px;
    padding-left: 20px;
    border-left: 2px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 8px 0;
}

.sub-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-top: none;
    margin-bottom: 0;
}

.sub-item:last-child {
    border-radius: 0 0 8px 0;
}

/* Different levels with different indentation and background */
.sub-items.level-2 {
    margin-left: 30px;
    background: #f8fafc;
}

.sub-items.level-3 {
    margin-left: 20px;
    background: #f1f5f9;
}

.sub-items.level-4 {
    margin-left: 15px;
    background: #e2e8f0;
}

.sub-item.level-2 {
    background: #f8fafc;
    font-size: 13px;
}

.sub-item.level-3 {
    background: #f1f5f9;
    font-size: 12px;
}

.sub-item.level-4 {
    background: #e2e8f0;
    font-size: 11px;
}

/* Adjust item name size for deeper levels */
.sub-item.level-2 .item-name {
    font-size: 13px;
}

.sub-item.level-3 .item-name {
    font-size: 12px;
}

.sub-item.level-4 .item-name {
    font-size: 11px;
}

/* Switch Toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d1d5db;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #356DF1;
}

input:checked + .slider:before {
    transform: translateX(20px);
}

/* Success Message */
.success-message {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #10b981;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 1000;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* No items message */
.no-items {
    padding: 20px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
}

.no-items p {
    margin: 0;
    font-size: 14px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
});

function initializeDragAndDrop() {
    const criteriaContainer = document.getElementById('filterCriteriaContainer');
    if (!criteriaContainer) {
        console.error('filterCriteriaContainer not found');
        return;
    }
    
    const criteria = criteriaContainer.querySelectorAll('.filter-criteria');
    console.log('Found criteria:', criteria.length);
    
    // Initialize mode-based drag and drop
    updateDragMode();
    
    // Add chevron toggle to criteria headers
    criteria.forEach(criterion => {
        const header = criterion.querySelector('.criteria-header');
        const title = header.querySelector('.criteria-title');
        
        // Add chevron icon
        const chevron = document.createElement('button');
        chevron.className = 'dropdown-toggle';
        chevron.innerHTML = '<i class="fas fa-chevron-down"></i>';
        chevron.style.cssText = 'background: none; border: none; cursor: pointer; padding: 4px; margin-left: 8px; color: #6b7280; transition: color 0.2s;';
        
        title.appendChild(chevron);
        
        // Toggle dropdown
        chevron.addEventListener('click', function() {
            const items = criterion.querySelector('.criteria-items');
            const icon = this.querySelector('i');
            
            if (items.style.display === 'none') {
                items.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
                criterion.classList.add('expanded');
            } else {
                items.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
                criterion.classList.remove('expanded');
            }
            
            // Update drag mode based on expanded state
            updateDragMode();
        });
        
        // Initially collapse all
        const items = criterion.querySelector('.criteria-items');
        if (items) {
            items.style.display = 'none';
        }
    });
}

function updateDragMode() {
    const criteriaContainer = document.getElementById('filterCriteriaContainer');
    const criteria = criteriaContainer.querySelectorAll('.filter-criteria');
    
    // Check if any criteria is expanded
    const hasExpanded = Array.from(criteria).some(c => c.classList.contains('expanded'));
    
    console.log('Drag mode update - hasExpanded:', hasExpanded);
    
    if (hasExpanded) {
        // MODE 2: Items level - lock criteria, enable items in expanded criteria only
        console.log('Switching to ITEMS drag mode');
        
        criteria.forEach(criterion => {
            if (criterion.classList.contains('expanded')) {
                // Disable criteria dragging
                criterion.draggable = false;
                criterion.style.cursor = 'default';
                criterion.removeEventListener('dragstart', criterion._dragStart);
                criterion.removeEventListener('dragend', criterion._dragEnd);
                
                // Enable items dragging in this expanded criteria only
                const items = criterion.querySelectorAll('.criteria-item');
                console.log('Enabling items for', criterion.dataset.criteria, ':', items.length);
                
                items.forEach(item => {
                    enableItemDragging(item, criterion);
                });
            } else {
                // Disable both criteria and items for collapsed criteria
                criterion.draggable = false;
                criterion.style.cursor = 'default';
                
                const items = criterion.querySelectorAll('.criteria-item');
                items.forEach(item => {
                    disableItemDragging(item);
                });
            }
        });
    } else {
        // MODE 1: Criteria level - enable criteria, disable all items
        console.log('Switching to CRITERIA drag mode');
        
        criteria.forEach(criterion => {
            // Enable criteria dragging
            enableCriteriaDragging(criterion, criteriaContainer);
            
            // Disable all items
            const items = criterion.querySelectorAll('.criteria-item');
            items.forEach(item => {
                disableItemDragging(item);
            });
        });
    }
}

function enableCriteriaDragging(criterion, container) {
    criterion.draggable = true;
    criterion.style.cursor = 'grab';
    
    // Remove existing listeners to avoid duplicates
    if (criterion._dragStart) {
        criterion.removeEventListener('dragstart', criterion._dragStart);
    }
    if (criterion._dragEnd) {
        criterion.removeEventListener('dragend', criterion._dragEnd);
    }
    
    // Store references to listeners
    criterion._dragStart = function(e) {
        console.log('🚀 Drag started: ' + criterion.dataset.criteria);
        
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', criterion.dataset.criteria);
        
        criterion.classList.add('dragging');
        
        // Create ghost image
        const ghost = criterion.cloneNode(true);
        ghost.style.position = 'absolute';
        ghost.style.top = '-1000px';
        ghost.style.opacity = '0.8';
        ghost.style.transform = 'rotate(5deg)';
        document.body.appendChild(ghost);
        
        e.dataTransfer.setDragImage(ghost, e.offsetX, e.offsetY);
        
        setTimeout(() => {
            document.body.removeChild(ghost);
        }, 0);
    };
    
    criterion._dragEnd = function(e) {
        console.log('🏁 Drag ended: ' + criterion.dataset.criteria);
        criterion.classList.remove('dragging');
        
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
    };
    
    criterion.addEventListener('dragstart', criterion._dragStart);
    criterion.addEventListener('dragend', criterion._dragEnd);
    
    // Add dragover and drop events
    criterion.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        criterion.classList.add('drag-over');
    });
    
    criterion.addEventListener('dragleave', function(e) {
        criterion.classList.remove('drag-over');
    });
    
    criterion.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        criterion.classList.remove('drag-over');
        
        const draggedId = e.dataTransfer.getData('text/plain');
        const draggedElement = container.querySelector(`[data-criteria="${draggedId}"]`);
        
        if (draggedElement && draggedElement !== criterion) {
            console.log('📦 Dropping ' + draggedId + ' onto ' + criterion.dataset.criteria);
            
            criterion.parentNode.insertBefore(draggedElement, criterion);
            
            criterion.style.background = '#c8e6c9';
            setTimeout(() => {
                criterion.style.background = '';
            }, 500);
            
            console.log('✅ Criteria DOM updated successfully!');
        }
    });
}

function disableCriteriaDragging(criterion) {
    criterion.draggable = false;
    criterion.style.cursor = 'default';
    
    if (criterion._dragStart) {
        criterion.removeEventListener('dragstart', criterion._dragStart);
    }
    if (criterion._dragEnd) {
        criterion.removeEventListener('dragend', criterion._dragEnd);
    }
}

function enableItemDragging(item, container) {
    const dragHandle = item.querySelector('.drag-handle');
    if (!dragHandle) {
        console.log('No drag handle found for item:', item.dataset.id);
        return;
    }
    
    // Make drag handle draggable
    dragHandle.draggable = true;
    dragHandle.style.cursor = 'grab';
    
    // Remove existing listeners
    if (dragHandle._dragStart) {
        dragHandle.removeEventListener('dragstart', dragHandle._dragStart);
    }
    if (dragHandle._dragEnd) {
        dragHandle.removeEventListener('dragend', dragHandle._dragEnd);
    }
    
    // Store references
    dragHandle._dragStart = function(e) {
        console.log('🚀 Drag started item: ' + item.dataset.id);
        
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', item.dataset.id);
        e.dataTransfer.setData('dragType', 'item');
        e.dataTransfer.setData('parentId', item.dataset.parent);
        
        item.classList.add('dragging');
        
        // Create ghost image
        const ghost = item.cloneNode(true);
        ghost.style.position = 'absolute';
        ghost.style.top = '-1000px';
        ghost.style.opacity = '0.8';
        ghost.style.transform = 'rotate(5deg)';
        document.body.appendChild(ghost);
        
        e.dataTransfer.setDragImage(ghost, e.offsetX, e.offsetY);
        
        setTimeout(() => {
            document.body.removeChild(ghost);
        }, 0);
        
        e.stopPropagation();
    };
    
    dragHandle._dragEnd = function(e) {
        console.log('🏁 Drag ended item: ' + item.dataset.id);
        item.classList.remove('dragging');
        
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
        
        e.stopPropagation();
    };
    
    dragHandle.addEventListener('dragstart', dragHandle._dragStart);
    dragHandle.addEventListener('dragend', dragHandle._dragEnd);
    
    // Make item droppable
    item.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        const dragType = e.dataTransfer.getData('dragType');
        if (dragType === 'item') {
            item.classList.add('drag-over');
        }
    });
    
    item.addEventListener('dragleave', function(e) {
        item.classList.remove('drag-over');
    });
    
    item.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        item.classList.remove('drag-over');
        
        const draggedId = e.dataTransfer.getData('text/plain');
        const dragType = e.dataTransfer.getData('dragType');
        const draggedElement = container.querySelector(`[data-id="${draggedId}"]`);
        
        if (dragType === 'item' && draggedElement && draggedElement !== item) {
            const dragParentId = e.dataTransfer.getData('parentId');
            const thisParentId = item.dataset.parent;
            
            if (dragParentId === thisParentId) {
                console.log('📦 Dropping item ' + draggedId + ' onto item ' + item.dataset.id);
                
                item.parentNode.insertBefore(draggedElement, item);
                
                item.style.background = '#c8e6c9';
                setTimeout(() => {
                    item.style.background = '';
                }, 500);
                
                console.log('✅ Item DOM updated successfully!');
            }
        }
    });
}

function disableItemDragging(item) {
    const dragHandle = item.querySelector('.drag-handle');
    if (dragHandle) {
        dragHandle.draggable = false;
        dragHandle.style.cursor = 'default';
        
        if (dragHandle._dragStart) {
            dragHandle.removeEventListener('dragstart', dragHandle._dragStart);
        }
        if (dragHandle._dragEnd) {
            dragHandle.removeEventListener('dragend', dragHandle._dragEnd);
        }
    }
}

function makeDraggable(element, container, type) {
    if (type === 'criteria') {
        // Apply EXACT same logic as test - make whole element draggable
        element.draggable = true;
        element.style.cursor = 'grab';
        
        element.addEventListener('dragstart', function(e) {
            console.log('🚀 Drag started: ' + element.dataset.criteria);
            
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', element.dataset.criteria);
            
            element.classList.add('dragging');
            
            // Create ghost image exactly like test
            const ghost = element.cloneNode(true);
            ghost.style.position = 'absolute';
            ghost.style.top = '-1000px';
            ghost.style.opacity = '0.8';
            ghost.style.transform = 'rotate(5deg)';
            document.body.appendChild(ghost);
            
            e.dataTransfer.setDragImage(ghost, e.offsetX, e.offsetY);
            
            setTimeout(() => {
                document.body.removeChild(ghost);
            }, 0);
        });
        
        element.addEventListener('dragend', function(e) {
            console.log('🏁 Drag ended: ' + element.dataset.criteria);
            element.classList.remove('dragging');
            
            // Remove all drag-over classes exactly like test
            document.querySelectorAll('.drag-over').forEach(el => {
                el.classList.remove('drag-over');
            });
        });
        
        element.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            element.classList.add('drag-over');
            console.log('🎯 Drag over: ' + element.dataset.criteria);
        });
        
        element.addEventListener('dragleave', function(e) {
            element.classList.remove('drag-over');
            console.log('👋 Drag leave: ' + element.dataset.criteria);
        });
        
        element.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            element.classList.remove('drag-over');
            
            const draggedId = e.dataTransfer.getData('text/plain');
            const draggedElement = container.querySelector(`[data-criteria="${draggedId}"]`);
            
            if (draggedElement && draggedElement !== element) {
                console.log('📦 Dropping ' + draggedId + ' onto ' + element.dataset.criteria);
                
                // Insert before the drop target - EXACT same as test
                this.parentNode.insertBefore(draggedElement, this);
                
                // Visual feedback exactly like test
                this.style.background = '#c8e6c9';
                setTimeout(() => {
                    this.style.background = '';
                }, 500);
                
                console.log('✅ DOM updated successfully!');
            } else {
                console.log('❌ Cannot drop on itself');
            }
        });
    } else {
        // For items - ONLY make drag-handle draggable, not the whole item
        const dragHandle = element.querySelector('.drag-handle');
        if (!dragHandle) {
            console.log('No drag handle found for item:', element.dataset.id);
            return;
        }
        
        // Make the drag handle draggable, not the whole item
        dragHandle.draggable = true;
        dragHandle.style.cursor = 'grab';
        element.style.cursor = 'default';
        
        // Prevent drag on the item itself, only allow on drag handle
        element.addEventListener('dragstart', function(e) {
            e.preventDefault();
            console.log('Prevented drag on item body - use drag handle only');
        });
        
        dragHandle.addEventListener('dragstart', function(e) {
            console.log('🚀 Drag started item: ' + element.dataset.id);
            
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', element.dataset.id);
            e.dataTransfer.setData('dragType', 'item');
            e.dataTransfer.setData('parentId', element.dataset.parent);
            
            element.classList.add('dragging');
            
            // Create ghost image
            const ghost = element.cloneNode(true);
            ghost.style.position = 'absolute';
            ghost.style.top = '-1000px';
            ghost.style.opacity = '0.8';
            ghost.style.transform = 'rotate(5deg)';
            document.body.appendChild(ghost);
            
            e.dataTransfer.setDragImage(ghost, e.offsetX, e.offsetY);
            
            setTimeout(() => {
                document.body.removeChild(ghost);
            }, 0);
            
            e.stopPropagation(); // Prevent bubbling to parent
        });
        
        dragHandle.addEventListener('dragend', function(e) {
            console.log('🏁 Drag ended item: ' + element.dataset.id);
            element.classList.remove('dragging');
            
            // Remove all drag-over classes
            document.querySelectorAll('.drag-over').forEach(el => {
                el.classList.remove('drag-over');
            });
            
            e.stopPropagation(); // Prevent bubbling to parent
        });
        
        // Make items droppable
        element.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const dragType = e.dataTransfer.getData('dragType');
            if (dragType === 'item') {
                element.classList.add('drag-over');
                console.log('🎯 Drag over item: ' + element.dataset.id);
            }
        });
        
        element.addEventListener('dragleave', function(e) {
            element.classList.remove('drag-over');
            console.log('👋 Drag leave item: ' + element.dataset.id);
        });
        
        element.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            element.classList.remove('drag-over');
            
            const draggedId = e.dataTransfer.getData('text/plain');
            const dragType = e.dataTransfer.getData('dragType');
            const draggedElement = container.querySelector(`[data-id="${draggedId}"]`);
            
            if (dragType === 'item' && draggedElement && draggedElement !== element) {
                const dragParentId = e.dataTransfer.getData('parentId');
                const thisParentId = element.dataset.parent;
                
                // Only allow drop within same parent level
                if (dragParentId === thisParentId) {
                    console.log('📦 Dropping item ' + draggedId + ' onto item ' + element.dataset.id);
                    
                    // Insert before the drop target
                    element.parentNode.insertBefore(draggedElement, element);
                    
                    // Visual feedback
                    element.style.background = '#c8e6c9';
                    setTimeout(() => {
                        element.style.background = '';
                    }, 500);
                    
                    console.log('✅ Item DOM updated successfully!');
                } else {
                    console.log('❌ Cannot drop - different parent levels');
                }
            } else {
                console.log('❌ Cannot drop on itself or invalid type');
            }
        });
    }
}


function saveFilterConfig() {
    const config = {
        criteria: [],
        items: {}
    };
    
    // Get criteria order
    const criteriaContainer = document.getElementById('filterCriteriaContainer');
    const criteria = criteriaContainer.querySelectorAll('.filter-criteria');
    
    criteria.forEach((criterion, index) => {
        const criteriaName = criterion.dataset.criteria;
        const enabled = criterion.querySelector('.criteria-header input[type="checkbox"]').checked;
        
        config.criteria.push({
            name: criteriaName,
            order: index + 1,
            enabled: enabled
        });
        
        // Get items order for this criteria
        config.items[criteriaName] = [];
        const items = criterion.querySelectorAll('.criteria-item');
        
        items.forEach((item, itemIndex) => {
            const itemId = item.dataset.id;
            const parentId = item.dataset.parent;
            const itemEnabled = item.querySelector('input[type="checkbox"]').checked;
            
            config.items[criteriaName].push({
                id: parseInt(itemId),
                parent_id: parseInt(parentId),
                order: itemIndex + 1,
                enabled: itemEnabled
            });
        });
    });
    
    // Send to server
    fetch('api.php?action=saveFilterConfig', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(config)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Not JSON, return text to see what we got
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
            });
        }
    })
    .then(data => {
        if (data.success) {
            showSuccessMessage();
        } else {
            alert('Lỗi lưu cấu hình: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving filter config:', error);
        alert('Lỗi kết nối server: ' + error.message);
    });
}

function resetFilterConfig() {
    if (confirm('Bạn có chắc chắn muốn reset cấu hình về mặc định?')) {
        fetch('api.php?action=resetFilterConfig', {
            method: 'POST'
        })
        .then(response => {
            console.log('Reset response status:', response.status);
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text().then(text => {
                    console.error('Reset response is not JSON:', text);
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                });
            }
        })
        .then(data => {
            if (data.success) {
                showSuccessMessage();
                location.reload();
            } else {
                alert('Lỗi reset cấu hình: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error resetting filter config:', error);
            alert('Lỗi kết nối server: ' + error.message);
        });
    }
}

function toggleChildren(button) {
    const subItems = button.closest('.criteria-item').querySelector('.sub-items');
    const icon = button.querySelector('i');
    
    if (subItems.style.display === 'none' || subItems.style.display === '') {
        subItems.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        button.classList.add('expanded');
    } else {
        subItems.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        button.classList.remove('expanded');
    }
}

function showSuccessMessage() {
    const message = document.getElementById('successMessage');
    message.style.display = 'flex';
    
    setTimeout(() => {
        message.style.display = 'none';
    }, 3000);
}
</script>
