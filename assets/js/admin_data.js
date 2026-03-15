/**
 * Admin Product Data Management JavaScript
 * Xử lý các chức năng tương tác cho trang quản lý dữ liệu sản phẩm
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initImportDropzone();
    initProductRowClick();
    initDeleteForms();
    initImportPreview();
});

/**
 * Initialize import dropzone functionality
 */
function initImportDropzone() {
    const dropzone = document.getElementById('importDropzone');
    const fileInput = document.getElementById('excelFile');
    const importBtn = document.getElementById('importBtn');
    
    if (!dropzone || !fileInput) return;
    
    // Click to open file dialog
    dropzone.addEventListener('click', function() {
        fileInput.click();
    });
    
    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleFileSelect(this.files[0]);
        }
    });
    
    // Drag and drop events
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    dropzone.addEventListener('dragleave', function() {
        this.classList.remove('dragover');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
}

/**
 * Handle file selection
 */
function handleFileSelect(file) {
    const validExtensions = ['xlsx', 'csv'];
    const ext = file.name.split('.').pop().toLowerCase();
    
    if (!validExtensions.includes(ext)) {
        alert('Định dạng file không hợp lệ. Vui lòng chọn file .xlsx hoặc .csv');
        return;
    }
    
    // Show file name
    const dropzone = document.getElementById('importDropzone');
    if (dropzone) {
        dropzone.innerHTML = `
            <i class="fas fa-file-excel"></i>
            <p>Đã chọn: ${file.name}</p>
            <small>Click để thay đổi file</small>
        `;
    }
    
    // Enable import button
    const importBtn = document.getElementById('importBtn');
    if (importBtn) {
        importBtn.disabled = false;
    }
    
    // Preview the file
    previewFile(file);
}

/**
 * Preview file before import
 */
function previewFile(file) {
    const formData = new FormData();
    formData.append('excel_file', file);
    formData.append('product_id', getQueryParam('product_id'));
    formData.append('preview', '1');
    
    fetch('?page=admin&module=products&action=data&ajax=import', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPreview(data.data);
        } else {
            alert(data.message || 'Lỗi đọc file');
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
    });
}

/**
 * Show preview table
 */
function showPreview(previewData) {
    const previewContainer = document.getElementById('importPreview');
    const tableHead = previewContainer.querySelector('#previewTable thead');
    const tableBody = previewContainer.querySelector('#previewTable tbody');
    
    if (!previewData || !previewData.preview) return;
    
    // Build header
    let headerHtml = '<tr>';
    const headers = previewData.headers || [];
    headers.forEach(h => {
        headerHtml += `<th>${h}</th>`;
    });
    headerHtml += '</tr>';
    tableHead.innerHTML = headerHtml;
    
    // Build body
    let bodyHtml = '';
    previewData.preview.forEach(row => {
        bodyHtml += '<tr>';
        headers.forEach(h => {
            bodyHtml += `<td>${row[h] || ''}</td>`;
        });
        bodyHtml += '</tr>';
    });
    tableBody.innerHTML = bodyHtml;
    
    // Show preview
    previewContainer.style.display = 'block';
    
    // Store data for confirm
    previewContainer.dataset.totalRows = previewData.total_rows;
}

/**
 * Initialize import preview actions
 */
function initImportPreview() {
    const confirmBtn = document.getElementById('confirmImport');
    const cancelBtn = document.getElementById('cancelImport');
    const fileInput = document.getElementById('excelFile');
    const previewContainer = document.getElementById('importPreview');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (!fileInput || !fileInput.files[0]) {
                alert('Vui lòng chọn file trước');
                return;
            }
            
            const formData = new FormData();
            formData.append('excel_file', fileInput.files[0]);
            formData.append('product_id', getQueryParam('product_id'));
            formData.append('replace', 'true');
            
            // Show loading
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang import...';
            
            fetch('?page=admin&module=products&action=data&ajax=import', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reload page to show new data
                    location.reload();
                } else {
                    alert(data.message || 'Lỗi import');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check"></i> Xác nhận Import';
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                alert('Lỗi import dữ liệu');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Xác nhận Import';
            });
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            previewContainer.style.display = 'none';
            
            // Reset file input
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Reset dropzone
            const dropzone = document.getElementById('importDropzone');
            if (dropzone) {
                dropzone.innerHTML = `
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Kéo thả file Excel vào đây hoặc click để chọn</p>
                    <small>Chấp nhận: .xlsx, .csv</small>
                `;
            }
            
            // Disable import button
            const importBtn = document.getElementById('importBtn');
            if (importBtn) {
                importBtn.disabled = true;
            }
        });
    }
}

/**
 * Initialize product row click to select product
 */
function initProductRowClick() {
    const productRows = document.querySelectorAll('.products-table tbody tr');
    
    productRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const productId = this.dataset.productId;
            if (productId) {
                window.location.href = `?page=admin&module=products&action=data&product_id=${productId}`;
            }
        });
    });
}

/**
 * Initialize delete forms with confirmation
 */
function initDeleteForms() {
    const deleteForms = document.querySelectorAll('.delete-form, .delete-all-form');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Confirmation is handled by onsubmit attribute
            // This is for additional processing if needed
        });
    });
}

/**
 * Get URL query parameter
 */
function getQueryParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
    
    const container = document.querySelector('.product-data-page');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 3000);
    }
}

/**
 * AJAX helper function
 */
function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    return fetch(url, options)
        .then(response => response.json())
        .catch(error => {
            console.error('AJAX Error:', error);
            throw error;
        });
}

/**
 * Format number with Vietnamese locale
 */
function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
