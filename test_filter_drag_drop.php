<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Drag & Drop Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .test-area {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
            cursor: grab;
            transition: all 0.2s;
        }
        
        .filter-criteria:hover {
            border-color: #356DF1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
        
        .drag-handle {
            cursor: grab;
            color: #9ca3af;
            transition: color 0.2s;
        }
        
        .drag-handle:hover {
            color: #356DF1;
        }
        
        .criteria-items {
            padding: 20px;
        }
        
        .criteria-item {
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: grab;
        }
        
        .criteria-item:hover {
            border-color: #356DF1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .criteria-item.dragging {
            opacity: 0.5;
            transform: scale(0.95);
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
        
        .console {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            height: 300px;
            overflow-y: auto;
        }
        
        .btn {
            background: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 5px;
        }
        
        .btn:hover {
            background: #1976d2;
        }
        
        .status {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Filter Drag & Drop Test</h1>
        
        <div class="status" id="status">
            <strong>Status:</strong> Ready to test
        </div>
        
        <div class="test-area">
            <h2>Kéo thả các filter criteria dưới đây:</h2>
            
            <div class="filter-criteria-container" id="filterCriteriaContainer">
                
                <!-- Categories Filter -->
                <div class="filter-criteria" data-criteria="categories" data-order="1">
                    <div class="criteria-header">
                        <div class="criteria-title">
                            <i class="fas fa-folder"></i>
                            <span>Danh Mục</span>
                            <span class="item-count">(5)</span>
                        </div>
                        <div class="criteria-controls">
                            <div class="drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                        </div>
                    </div>
                    <div class="criteria-items">
                        <div class="criteria-item" data-id="1" data-parent="0">
                            <div class="item-content">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                                <div class="item-info">
                                    <span class="item-name">Điện thoại</span>
                                    <span class="item-count">(120)</span>
                                </div>
                            </div>
                        </div>
                        <div class="criteria-item" data-id="2" data-parent="0">
                            <div class="item-content">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                                <div class="item-info">
                                    <span class="item-name">Laptop</span>
                                    <span class="item-count">(85)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Brands Filter -->
                <div class="filter-criteria" data-criteria="brands" data-order="2">
                    <div class="criteria-header">
                        <div class="criteria-title">
                            <i class="fas fa-tag"></i>
                            <span>Thương Hiệu</span>
                            <span class="item-count">(8)</span>
                        </div>
                        <div class="criteria-controls">
                            <div class="drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                        </div>
                    </div>
                    <div class="criteria-items">
                        <div class="criteria-item" data-id="1" data-parent="0">
                            <div class="item-content">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                                <div class="item-info">
                                    <span class="item-name">Apple</span>
                                    <span class="item-count">(45)</span>
                                </div>
                            </div>
                        </div>
                        <div class="criteria-item" data-id="2" data-parent="0">
                            <div class="item-content">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                                <div class="item-info">
                                    <span class="item-name">Samsung</span>
                                    <span class="item-count">(38)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Price Ranges Filter -->
                <div class="filter-criteria" data-criteria="price_ranges" data-order="3">
                    <div class="criteria-header">
                        <div class="criteria-title">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Khoảng Giá</span>
                            <span class="item-count">(4)</span>
                        </div>
                        <div class="criteria-controls">
                            <div class="drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </div>
                        </div>
                    </div>
                    <div class="criteria-items">
                        <div class="criteria-item" data-id="1" data-parent="0">
                            <div class="item-content">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                                <div class="item-info">
                                    <span class="item-name">Dưới 1 triệu</span>
                                </div>
                            </div>
                        </div>
                        <div class="criteria-item" data-id="2" data-parent="0">
                            <div class="item-content">
                                <div class="drag-handle">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                                <div class="item-info">
                                    <span class="item-name">1-5 triệu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <div class="test-area">
            <h2>Controls:</h2>
            <button class="btn" onclick="resetOrder()">Reset Order</button>
            <button class="btn" onclick="getCurrentOrder()">Show Current Order</button>
            <button class="btn" onclick="clearConsole()">Clear Console</button>
            <button class="btn" onclick="testDragEvents()">Test Drag Events</button>
        </div>
        
        <div class="test-area">
            <h2>Console Log:</h2>
            <div class="console" id="console"></div>
        </div>
    </div>

    <script>
        let consoleDiv = document.getElementById('console');
        let statusDiv = document.getElementById('status');
        
        function log(message) {
            consoleDiv.innerHTML += `[${new Date().toLocaleTimeString()}] ${message}<br>`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }
        
        function updateStatus(message) {
            statusDiv.innerHTML = `<strong>Status:</strong> ${message}`;
        }
        
        function clearConsole() {
            consoleDiv.innerHTML = '';
            log('Console cleared');
        }
        
        function getCurrentOrder() {
            const container = document.getElementById('filterCriteriaContainer');
            const criteria = container.querySelectorAll('.filter-criteria');
            const order = Array.from(criteria).map(item => item.dataset.criteria);
            log('Current criteria order: ' + order.join(' -> '));
            updateStatus('Order retrieved: ' + order.join(', '));
        }
        
        function resetOrder() {
            const container = document.getElementById('filterCriteriaContainer');
            const criteria = Array.from(document.querySelectorAll('.filter-criteria'));
            
            criteria.sort((a, b) => parseInt(a.dataset.order) - parseInt(b.dataset.order));
            
            criteria.forEach(item => container.appendChild(item));
            
            log('Order reset to default');
            getCurrentOrder();
            updateStatus('Order reset to default');
        }
        
        function testDragEvents() {
            const criteria = document.querySelectorAll('.filter-criteria');
            log('Testing drag events on ' + criteria.length + ' criteria...');
            
            criteria.forEach((criterion, index) => {
                log(`Criterion ${index + 1}: ${criterion.dataset.criteria}`);
                log(`  - Draggable: ${criterion.draggable}`);
                log(`  - Cursor: ${criterion.style.cursor}`);
                
                // Check event listeners
                const hasDragStart = criterion.getAttribute('data-dragstart') !== null;
                const hasDragEnd = criterion.getAttribute('data-dragend') !== null;
                log(`  - Has dragstart event: ${hasDragStart}`);
                log(`  - Has dragend event: ${hasDragEnd}`);
            });
            
            updateStatus('Drag events test completed');
        }
        
        // Initialize drag and drop - EXACT same as test_drag_drop.php
        document.addEventListener('DOMContentLoaded', function() {
            log('Initializing filter drag and drop...');
            updateStatus('Initializing drag and drop...');
            
            const criteriaContainer = document.getElementById('filterCriteriaContainer');
            if (!criteriaContainer) {
                log('❌ filterCriteriaContainer not found');
                updateStatus('ERROR: Container not found');
                return;
            }
            
            const criteria = criteriaContainer.querySelectorAll('.filter-criteria');
            log('Found criteria:', criteria.length);
            
            criteria.forEach((criterion, index) => {
                log('Setting up criterion:', criterion.dataset.criteria);
                
                // Make draggable - EXACT same as test
                criterion.draggable = true;
                criterion.style.cursor = 'grab';
                
                // Drag start - EXACT same as test
                criterion.addEventListener('dragstart', function(e) {
                    log('🚀 Drag started: ' + this.dataset.criteria);
                    updateStatus('Dragging: ' + this.dataset.criteria);
                    
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', this.dataset.criteria);
                    
                    this.classList.add('dragging');
                    
                    // Create ghost image exactly like test
                    const ghost = this.cloneNode(true);
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
                
                // Drag end - EXACT same as test
                criterion.addEventListener('dragend', function(e) {
                    log('🏁 Drag ended: ' + this.dataset.criteria);
                    updateStatus('Drag ended: ' + this.dataset.criteria);
                    this.classList.remove('dragging');
                    
                    // Remove all drag-over classes exactly like test
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                });
                
                // Drag over - EXACT same as test
                criterion.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    
                    this.classList.add('drag-over');
                    log('🎯 Drag over: ' + this.dataset.criteria);
                });
                
                // Drag leave - EXACT same as test
                criterion.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                    log('👋 Drag leave: ' + this.dataset.criteria);
                });
                
                // Drop - EXACT same as test
                criterion.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    this.classList.remove('drag-over');
                    
                    const draggedId = e.dataTransfer.getData('text/plain');
                    const draggedElement = criteriaContainer.querySelector(`[data-criteria="${draggedId}"]`);
                    
                    if (draggedElement && draggedElement !== this) {
                        log('📦 Dropping ' + draggedId + ' onto ' + this.dataset.criteria);
                        updateStatus('Dropped ' + draggedId + ' onto ' + this.dataset.criteria);
                        
                        // Insert before the drop target - EXACT same as test
                        this.parentNode.insertBefore(draggedElement, this);
                        
                        // Visual feedback exactly like test
                        this.style.background = '#c8e6c9';
                        setTimeout(() => {
                            this.style.background = '';
                        }, 500);
                        
                        log('✅ DOM updated successfully!');
                        getCurrentOrder();
                    } else {
                        log('❌ Cannot drop on itself');
                        updateStatus('Cannot drop on itself');
                    }
                });
            });
            
            log('✅ Drag and drop initialized for ' + criteria.length + ' criteria');
            updateStatus('Ready - Drag and drop initialized');
            getCurrentOrder();
        });
    </script>
</body>
</html>
