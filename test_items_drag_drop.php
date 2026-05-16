<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Drag & Drop Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .test-area {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .criteria-container {
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
        
        .criteria-item.drag-over {
            background: #f0f9ff;
            border-color: #3b82f6;
        }
        
        .item-content {
            display: flex;
            align-items: center;
            padding: 12px 16px;
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
        <h1>🧪 Items Drag & Drop Test</h1>
        
        <div class="status" id="status">
            <strong>Status:</strong> Ready to test items drag & drop
        </div>
        
        <div class="test-area">
            <h2>Kéo thả các items bên trong danh mục:</h2>
            
            <div class="criteria-container">
                <div class="criteria-header">
                    <div class="criteria-title">
                        <i class="fas fa-folder"></i>
                        <span>Danh Mục</span>
                    </div>
                </div>
                <div class="criteria-items" id="itemsContainer">
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
                    <div class="criteria-item" data-id="3" data-parent="0">
                        <div class="item-content">
                            <div class="drag-handle">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="item-info">
                                <span class="item-name">Máy tính bảng</span>
                                <span class="item-count">(45)</span>
                            </div>
                        </div>
                    </div>
                    <div class="criteria-item" data-id="4" data-parent="0">
                        <div class="item-content">
                            <div class="drag-handle">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="item-info">
                                <span class="item-name">Đồng hồ thông minh</span>
                                <span class="item-count">(32)</span>
                            </div>
                        </div>
                    </div>
                    <div class="criteria-item" data-id="5" data-parent="0">
                        <div class="item-content">
                            <div class="drag-handle">
                                <i class="fas fa-grip-lines"></i>
                            </div>
                            <div class="item-info">
                                <span class="item-name">Tai nghe</span>
                                <span class="item-count">(67)</span>
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
            const container = document.getElementById('itemsContainer');
            const items = container.querySelectorAll('.criteria-item');
            const order = Array.from(items).map(item => item.dataset.id + ':' + item.querySelector('.item-name').textContent);
            log('Current items order: ' + order.join(' -> '));
            updateStatus('Order retrieved');
        }
        
        function resetOrder() {
            const container = document.getElementById('itemsContainer');
            const items = Array.from(document.querySelectorAll('.criteria-item'));
            
            items.sort((a, b) => parseInt(a.dataset.id) - parseInt(b.dataset.id));
            
            items.forEach(item => container.appendChild(item));
            
            log('Order reset to default');
            getCurrentOrder();
            updateStatus('Order reset to default');
        }
        
        // Initialize items drag and drop - SIMPLIFIED version
        document.addEventListener('DOMContentLoaded', function() {
            log('Initializing items drag and drop...');
            updateStatus('Initializing items drag and drop...');
            
            const container = document.getElementById('itemsContainer');
            if (!container) {
                log('❌ itemsContainer not found');
                updateStatus('ERROR: Container not found');
                return;
            }
            
            const items = container.querySelectorAll('.criteria-item');
            log('Found items:', items.length);
            
            items.forEach((item, index) => {
                log('Setting up item:', item.dataset.id, '-', item.querySelector('.item-name').textContent);
                
                // Make draggable - SIMPLE approach like test_drag_drop.php
                item.draggable = true;
                item.style.cursor = 'grab';
                
                // Drag start - EXACT same as test_drag_drop.php
                item.addEventListener('dragstart', function(e) {
                    log('🚀 Drag started item: ' + this.dataset.id);
                    updateStatus('Dragging item: ' + this.querySelector('.item-name').textContent);
                    
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', this.dataset.id);
                    
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
                
                // Drag end - EXACT same as test_drag_drop.php
                item.addEventListener('dragend', function(e) {
                    log('🏁 Drag ended item: ' + this.dataset.id);
                    updateStatus('Drag ended item: ' + this.querySelector('.item-name').textContent);
                    this.classList.remove('dragging');
                    
                    // Remove all drag-over classes exactly like test
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                });
                
                // Drag over - EXACT same as test_drag_drop.php
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    
                    this.classList.add('drag-over');
                    log('🎯 Drag over item: ' + this.dataset.id);
                });
                
                // Drag leave - EXACT same as test_drag_drop.php
                item.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                    log('👋 Drag leave item: ' + this.dataset.id);
                });
                
                // Drop - EXACT same as test_drag_drop.php
                item.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    this.classList.remove('drag-over');
                    
                    const draggedId = e.dataTransfer.getData('text/plain');
                    const draggedElement = container.querySelector(`[data-id="${draggedId}"]`);
                    
                    if (draggedElement && draggedElement !== this) {
                        log('📦 Dropping item ' + draggedId + ' onto item ' + this.dataset.id);
                        updateStatus('Dropped ' + draggedElement.querySelector('.item-name').textContent + ' onto ' + this.querySelector('.item-name').textContent);
                        
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
            
            log('✅ Items drag and drop initialized for ' + items.length + ' items');
            updateStatus('Ready - Items drag and drop initialized');
            getCurrentOrder();
        });
    </script>
</body>
</html>
