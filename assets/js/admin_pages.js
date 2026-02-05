// Admin Pages Common JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all admin page functionality
    initTableSorting();
    initSearchFilter();
    initPagination();
    initModals();
    initFormValidation();
    initConfirmDialogs();
    initTooltips();
    
    // Table sorting functionality
    function initTableSorting() {
        const sortableHeaders = document.querySelectorAll('.table th[data-sort]');
        
        sortableHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const column = this.dataset.sort;
                const table = this.closest('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                // Determine sort direction
                const currentDirection = this.dataset.direction || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                
                // Clear all sort indicators
                sortableHeaders.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                    delete h.dataset.direction;
                });
                
                // Set new sort indicator
                this.classList.add(`sort-${newDirection}`);
                this.dataset.direction = newDirection;
                
                // Sort rows
                rows.sort((a, b) => {
                    const aValue = a.children[column].textContent.trim();
                    const bValue = b.children[column].textContent.trim();
                    
                    // Try to parse as numbers
                    const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
                    const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
                    
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return newDirection === 'asc' ? aNum - bNum : bNum - aNum;
                    }
                    
                    // String comparison
                    return newDirection === 'asc' 
                        ? aValue.localeCompare(bValue)
                        : bValue.localeCompare(aValue);
                });
                
                // Reorder DOM
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    }
    
    // Search and filter functionality
    function initSearchFilter() {
        const searchInputs = document.querySelectorAll('[data-search-target]');
        
        searchInputs.forEach(input => {
            let searchTimeout;
            
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.toLowerCase().trim();
                const targetSelector = this.dataset.searchTarget;
                const targets = document.querySelectorAll(targetSelector);
                
                searchTimeout = setTimeout(() => {
                    targets.forEach(target => {
                        const text = target.textContent.toLowerCase();
                        const shouldShow = query === '' || text.includes(query);
                        
                        target.style.display = shouldShow ? '' : 'none';
                    });
                    
                    updateSearchResults(targets, query);
                }, 300);
            });
        });
    }
    
    function updateSearchResults(targets, query) {
        const visibleCount = Array.from(targets).filter(t => t.style.display !== 'none').length;
        const resultElement = document.querySelector('.search-results');
        
        if (resultElement) {
            if (query) {
                resultElement.textContent = `Tìm thấy ${visibleCount} kết quả cho "${query}"`;
                resultElement.style.display = 'block';
            } else {
                resultElement.style.display = 'none';
            }
        }
    }
    
    // Pagination functionality
    function initPagination() {
        const paginationLinks = document.querySelectorAll('.page-link[data-page]');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const page = this.dataset.page;
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('page_num', page);
                
                // Add loading state
                this.classList.add('loading');
                
                // Navigate to new page
                window.location.href = currentUrl.toString();
            });
        });
    }
    
    // Modal functionality
    function initModals() {
        const modalTriggers = document.querySelectorAll('[data-modal-target]');
        const modalCloses = document.querySelectorAll('[data-modal-close]');
        
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.dataset.modalTarget;
                const modal = document.getElementById(targetId);
                
                if (modal) {
                    showModal(modal);
                }
            });
        });
        
        modalCloses.forEach(close => {
            close.addEventListener('click', function() {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    hideModal(modal);
                }
            });
        });
        
        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                hideModal(e.target);
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal-overlay.show');
                if (openModal) {
                    hideModal(openModal);
                }
            }
        });
    }
    
    function showModal(modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
    
    function hideModal(modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Form validation
    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                form.classList.add('was-validated');
                
                // Custom validation feedback
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    validateInput(input);
                });
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => validateInput(input));
                input.addEventListener('input', () => {
                    if (input.classList.contains('is-invalid')) {
                        validateInput(input);
                    }
                });
            });
        });
    }
    
    function validateInput(input) {
        const isValid = input.checkValidity();
        
        input.classList.remove('is-valid', 'is-invalid');
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
        
        // Show/hide feedback
        const feedback = input.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = isValid ? 'none' : 'block';
        }
    }
    
    // Confirmation dialogs
    function initConfirmDialogs() {
        const confirmButtons = document.querySelectorAll('[data-confirm]');
        
        confirmButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const message = this.dataset.confirm || 'Bạn có chắc chắn muốn thực hiện hành động này?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
                
                // Add loading state
                this.classList.add('loading');
                this.disabled = true;
            });
        });
    }
    
    // Tooltips (simple implementation)
    function initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                showTooltip(this);
            });
            
            element.addEventListener('mouseleave', function() {
                hideTooltip();
            });
        });
    }
    
    function showTooltip(element) {
        const text = element.dataset.tooltip;
        const tooltip = document.createElement('div');
        
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: #374151;
            color: white;
            padding: 6px 8px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 10000;
            pointer-events: none;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        
        element._tooltip = tooltip;
    }
    
    function hideTooltip() {
        const tooltips = document.querySelectorAll('.tooltip');
        tooltips.forEach(tooltip => tooltip.remove());
    }
    
    // Auto-save functionality for forms
    function initAutoSave() {
        const autoSaveForms = document.querySelectorAll('[data-auto-save]');
        
        autoSaveForms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            let saveTimeout;
            
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    clearTimeout(saveTimeout);
                    
                    saveTimeout = setTimeout(() => {
                        saveFormData(form);
                    }, 2000);
                });
            });
        });
    }
    
    function saveFormData(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Save to localStorage
        const formId = form.id || 'auto-save-form';
        localStorage.setItem(`auto-save-${formId}`, JSON.stringify(data));
        
        // Show save indicator
        showSaveIndicator('Đã lưu tự động');
    }
    
    function showSaveIndicator(message) {
        const indicator = document.createElement('div');
        indicator.textContent = message;
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10B981;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(indicator);
        
        setTimeout(() => indicator.style.opacity = '1', 100);
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => indicator.remove(), 300);
        }, 2000);
    }
    
    // Initialize auto-save
    initAutoSave();
    
    // Bulk actions functionality
    function initBulkActions() {
        const selectAllCheckbox = document.querySelector('#selectAll');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const bulkActionSelect = document.querySelector('#bulkAction');
        const bulkActionButton = document.querySelector('#bulkActionButton');
        
        if (selectAllCheckbox && itemCheckboxes.length > 0) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionState();
            });
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllState();
                    updateBulkActionState();
                });
            });
        }
        
        function updateSelectAllState() {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            const totalCount = itemCheckboxes.length;
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            }
        }
        
        function updateBulkActionState() {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            
            if (bulkActionSelect && bulkActionButton) {
                bulkActionSelect.disabled = checkedCount === 0;
                bulkActionButton.disabled = checkedCount === 0;
            }
        }
    }
    
    // Initialize bulk actions
    initBulkActions();
});