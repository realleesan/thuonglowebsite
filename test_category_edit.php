<?php
/**
 * Test file to check if form-actions HTML is being output
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate the view output
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/assets/css/admin_categories.css?v=<?= time() ?>">
    <style>
        /* Debug: Force show all elements */
        .form-actions { 
            border: 3px solid red !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .tab-pane {
            border: 2px dashed blue;
            min-height: 50px;
        }
    </style>
</head>
<body>
    <h2>Test Category Edit Form</h2>
    
    <div class="category-details-tabs">
        <div class="tabs-header">
            <button type="button" class="tab-btn active" data-tab="tab-basic">Tab 1</button>
            <button type="button" class="tab-btn" data-tab="tab-image">Tab 2</button>
        </div>
        <div class="tabs-content">
            <div class="tab-pane active" id="tab-basic">
                <p>Tab Basic Content</p>
            </div>
            <div class="tab-pane" id="tab-image">
                <p>Tab Image Content</p>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Cập Nhật Danh Mục
        </button>
        <button type="button" class="btn btn-secondary">
            <i class="fas fa-undo"></i>
            Đặt lại
        </button>
    </div>
    
    <hr>
    <p>If you see RED border around buttons and BLUE borders around tabs, CSS is loading.</p>
    <p><a href="test_category_edit.php">Refresh with cache bust</a></p>
</body>
</html>
