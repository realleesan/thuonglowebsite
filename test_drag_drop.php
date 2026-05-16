<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drag & Drop Test</title>
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
        
        .draggable-item {
            background: #e3f2fd;
            border: 2px solid #2196f3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            cursor: grab;
            transition: all 0.2s;
        }
        
        .draggable-item:hover {
            background: #bbdefb;
            transform: translateY(-2px);
        }
        
        .draggable-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            cursor: grabbing;
        }
        
        .draggable-item.drag-over {
            background: #c8e6c9;
            border-color: #4caf50;
            transform: scale(1.05);
        }
        
        .console {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            height: 200px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Drag & Drop Test</h1>
        
        <div class="test-area">
            <h2>Kéo thả các box dưới đây:</h2>
            
            <div class="draggable-item" data-id="1">
                📦 Box 1 - Kéo tôi!
            </div>
            
            <div class="draggable-item" data-id="2">
                📦 Box 2 - Kéo tôi!
            </div>
            
            <div class="draggable-item" data-id="3">
                📦 Box 3 - Kéo tôi!
            </div>
            
            <div class="draggable-item" data-id="4">
                📦 Box 4 - Kéo tôi!
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
        
        function log(message) {
            consoleDiv.innerHTML += `[${new Date().toLocaleTimeString()}] ${message}<br>`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }
        
        function clearConsole() {
            consoleDiv.innerHTML = '';
            log('Console cleared');
        }
        
        function getCurrentOrder() {
            const items = document.querySelectorAll('.draggable-item');
            const order = Array.from(items).map(item => item.dataset.id);
            log('Current order: ' + order.join(' -> '));
        }
        
        function resetOrder() {
            const container = document.querySelector('.test-area');
            const items = Array.from(document.querySelectorAll('.draggable-item'));
            
            items.sort((a, b) => parseInt(a.dataset.id) - parseInt(b.dataset.id));
            
            items.forEach(item => container.appendChild(item));
            
            log('Order reset to default');
            getCurrentOrder();
        }
        
        // Initialize drag and drop
        document.addEventListener('DOMContentLoaded', function() {
            log('Initializing drag and drop...');
            
            const items = document.querySelectorAll('.draggable-item');
            
            items.forEach(item => {
                // Make draggable
                item.draggable = true;
                
                // Drag start
                item.addEventListener('dragstart', function(e) {
                    log('🚀 Drag started: Box ' + this.dataset.id);
                    
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', this.dataset.id);
                    
                    this.classList.add('dragging');
                    
                    // Create ghost image
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
                
                // Drag end
                item.addEventListener('dragend', function(e) {
                    log('🏁 Drag ended: Box ' + this.dataset.id);
                    this.classList.remove('dragging');
                    
                    // Remove all drag-over classes
                    document.querySelectorAll('.drag-over').forEach(el => {
                        el.classList.remove('drag-over');
                    });
                });
                
                // Drag over
                item.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    
                    this.classList.add('drag-over');
                    log('🎯 Drag over: Box ' + this.dataset.id);
                });
                
                // Drag leave
                item.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                    log('👋 Drag leave: Box ' + this.dataset.id);
                });
                
                // Drop
                item.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    this.classList.remove('drag-over');
                    
                    const draggedId = e.dataTransfer.getData('text/plain');
                    const draggedElement = document.querySelector(`[data-id="${draggedId}"]`);
                    
                    if (draggedElement && draggedElement !== this) {
                        log('📦 Dropping Box ' + draggedId + ' onto Box ' + this.dataset.id);
                        
                        // Insert before the drop target
                        this.parentNode.insertBefore(draggedElement, this);
                        
                        // Visual feedback
                        this.style.background = '#c8e6c9';
                        setTimeout(() => {
                            this.style.background = '';
                        }, 500);
                        
                        log('✅ DOM updated successfully!');
                        getCurrentOrder();
                    } else {
                        log('❌ Cannot drop on itself');
                    }
                });
            });
            
            log('✅ Drag and drop initialized for ' + items.length + ' items');
            getCurrentOrder();
        });
    </script>
</body>
</html>
