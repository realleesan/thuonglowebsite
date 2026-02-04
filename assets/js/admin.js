/**
 * Admin Panel - Main JavaScript
 * Handles general admin functionality
 */

class AdminPanel {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupFormValidation();
        this.setupConfirmDialogs();
    }

    setupEventListeners() {
        // Handle responsive table scrolling
        this.setupResponsiveTables();
        
        // Handle search functionality
        this.setupSearch();
        
        // Handle form submissions
        this.setupFormHandlers();
        
        // Handle status changes
        this.setupStatusHandlers();
    }

    initializeComponents() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize loading states
        this.initLoadingStates();
        
        // Initialize pagination
        this.initPagination();
    }

    setupResponsiveTables() {
        const tables = document.querySelectorAll('.admin-table');
        tables.forEach(table => {
            if (!table.parentElement.classList.contains('admin-table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'admin-table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }

    setupSearch() {
        const searchInputs = document.querySelectorAll('.admin-search-input');
        searchInputs.forEach(input => {
            let timeout;
            input.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.performSearch(e.target.value, e.target.dataset.target);
                }, 300);
            });
        });
    }

    performSearch(query, target) {
        const targetElement = document.querySelector(target || '.admin-table tbody');
        if (!targetElement) return;

        const rows = targetElement.querySelectorAll('tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(query.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });

        // Update empty state
        this.updateEmptyState(targetElement);
    }

    updateEmptyState(container) {
        const visibleRows = container.querySelectorAll('tr:not([style*="display: none"])');
        let emptyState = container.parentElement.querySelector('.admin-empty-state');
        
        if (visibleRows.length === 0) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'admin-empty-state';
                emptyState.innerHTML = `
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc</p>
                `;
                container.parentElement.appendChild(emptyState);
            }
            emptyState.style.display = 'block';
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    setupFormHandlers() {
        const forms = document.querySelectorAll('.admin-form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    return false;
                }
                this.showLoadingState(form);
            });
        });
    }

    setupFormValidation() {
        const requiredFields = document.querySelectorAll('.admin-form-control[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
            
            field.addEventListener('input', () => {
                this.clearFieldError(field);
            });
        });
    }

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('.admin-form-control[required]');
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.dataset.name || field.name || 'Trường này';
        
        // Clear previous errors
        this.clearFieldError(field);
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, `${fieldName} không được để trống`);
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(field, 'Email không hợp lệ');
                return false;
            }
        }
        
        // Number validation
        if (field.type === 'number' && value) {
            if (isNaN(value) || parseFloat(value) < 0) {
                this.showFieldError(field, 'Giá trị phải là số dương');
                return false;
            }
        }
        
        // Date validation
        if (field.type === 'date' && value) {
            const date = new Date(value);
            if (isNaN(date.getTime())) {
                this.showFieldError(field, 'Ngày không hợp lệ');
                return false;
            }
        }

        return true;
    }

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorElement = field.parentElement.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            errorElement.style.cssText = 'color: #dc3545; font-size: 12px; margin-top: 4px;';
            field.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorElement = field.parentElement.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    setupConfirmDialogs() {
        const deleteButtons = document.querySelectorAll('.admin-btn-danger[data-confirm]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const message = button.dataset.confirm || 'Bạn có chắc chắn muốn xóa?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    setupStatusHandlers() {
        const statusSelects = document.querySelectorAll('.status-select');
        statusSelects.forEach(select => {
            select.addEventListener('change', (e) => {
                this.updateStatus(e.target.dataset.id, e.target.value, e.target.dataset.type);
            });
        });
    }

    updateStatus(id, status, type) {
        // Show loading
        const select = document.querySelector(`[data-id="${id}"][data-type="${type}"]`);
        const originalValue = select.value;
        
        // Here you would make an AJAX call to update the status
        // For now, we'll just show a success message
        this.showNotification('Cập nhật trạng thái thành công', 'success');
    }

    showLoadingState(element) {
        const loadingHtml = '<span class="admin-spinner"></span> Đang xử lý...';
        
        if (element.tagName === 'FORM') {
            const submitButton = element.querySelector('button[type="submit"], input[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.innerHTML;
                submitButton.innerHTML = loadingHtml;
            }
        } else {
            element.disabled = true;
            element.dataset.originalText = element.innerHTML;
            element.innerHTML = loadingHtml;
        }
    }

    hideLoadingState(element) {
        if (element.tagName === 'FORM') {
            const submitButton = element.querySelector('button[type="submit"], input[type="submit"]');
            if (submitButton && submitButton.dataset.originalText) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButton.dataset.originalText;
                delete submitButton.dataset.originalText;
            }
        } else if (element.dataset.originalText) {
            element.disabled = false;
            element.innerHTML = element.dataset.originalText;
            delete element.dataset.originalText;
        }
    }

    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target, e.target.dataset.tooltip);
            });
            
            element.addEventListener('mouseleave', () => {
                this.hideTooltip();
            });
        });
    }

    showTooltip(element, text) {
        let tooltip = document.querySelector('.admin-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.className = 'admin-tooltip';
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 6px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s ease;
            `;
            document.body.appendChild(tooltip);
        }
        
        tooltip.textContent = text;
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        tooltip.style.opacity = '1';
    }

    hideTooltip() {
        const tooltip = document.querySelector('.admin-tooltip');
        if (tooltip) {
            tooltip.style.opacity = '0';
        }
    }

    initLoadingStates() {
        // Add loading states to async operations
        const asyncButtons = document.querySelectorAll('[data-async]');
        asyncButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.showLoadingState(button);
                
                // Auto-hide after 3 seconds (adjust based on actual operation)
                setTimeout(() => {
                    this.hideLoadingState(button);
                }, 3000);
            });
        });
    }

    initPagination() {
        const paginationLinks = document.querySelectorAll('.admin-pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.dataset.page;
                if (page) {
                    this.loadPage(page);
                }
            });
        });
    }

    loadPage(page) {
        // Here you would implement pagination loading
        // For now, just show a loading state
        const tableContainer = document.querySelector('.admin-table-container');
        if (tableContainer) {
            tableContainer.innerHTML = '<div class="admin-loading"><span class="admin-spinner"></span> Đang tải...</div>';
            
            // Simulate loading
            setTimeout(() => {
                location.reload(); // Temporary - replace with actual pagination
            }, 1000);
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `admin-notification admin-notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            z-index: 1001;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        // Set background color based on type
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        notification.style.backgroundColor = colors[type] || colors.info;
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.parentElement.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize admin panel when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminPanel = new AdminPanel();
});

// Utility functions
window.AdminUtils = {
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    },
    
    formatDate: (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    },
    
    formatDateTime: (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN');
    },
    
    truncateText: (text, length = 100) => {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    }
};

/**
 * Dashboard Specific Functionality
 */
class AdminDashboard {
    constructor() {
        if (document.querySelector('.admin-dashboard')) {
            this.init();
        }
    }

    init() {
        this.setupAnimations();
        this.setupInteractiveElements();
        this.setupChartAnimations();
        this.setupRefreshFunctionality();
        this.setupRealTimeUpdates();
    }

    setupAnimations() {
        // Animate stat cards on load
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animate widgets
        const widgets = document.querySelectorAll('.dashboard-widget');
        widgets.forEach((widget, index) => {
            widget.style.opacity = '0';
            widget.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                widget.style.transition = 'all 0.8s ease';
                widget.style.opacity = '1';
                widget.style.transform = 'translateY(0)';
            }, 200 + (index * 150));
        });
    }

    setupInteractiveElements() {
        // Add hover effects to stat cards
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.animateStatCard(card, true);
            });
            
            card.addEventListener('mouseleave', () => {
                this.animateStatCard(card, false);
            });
        });

        // Add click effects to quick action buttons
        const quickActions = document.querySelectorAll('.quick-action-btn');
        quickActions.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.animateButtonClick(btn);
            });
        });

        // Add refresh functionality to widgets
        const widgetHeaders = document.querySelectorAll('.widget-header');
        widgetHeaders.forEach(header => {
            const refreshBtn = this.createRefreshButton();
            header.appendChild(refreshBtn);
            
            refreshBtn.addEventListener('click', () => {
                this.refreshWidget(header.parentElement);
            });
        });
    }

    animateStatCard(card, isHover) {
        const icon = card.querySelector('.stat-icon');
        const content = card.querySelector('.stat-content');
        
        if (isHover) {
            icon.style.transform = 'scale(1.1) rotate(5deg)';
            content.style.transform = 'translateX(5px)';
        } else {
            icon.style.transform = 'scale(1) rotate(0deg)';
            content.style.transform = 'translateX(0)';
        }
    }

    animateButtonClick(button) {
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
    }

    createRefreshButton() {
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'widget-refresh-btn';
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
        refreshBtn.style.cssText = `
            background: none;
            border: none;
            color: #718096;
            font-size: 14px;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
            float: right;
        `;
        
        refreshBtn.addEventListener('mouseenter', () => {
            refreshBtn.style.color = '#4a5568';
            refreshBtn.style.backgroundColor = '#edf2f7';
        });
        
        refreshBtn.addEventListener('mouseleave', () => {
            refreshBtn.style.color = '#718096';
            refreshBtn.style.backgroundColor = 'transparent';
        });
        
        return refreshBtn;
    }

    refreshWidget(widget) {
        const refreshBtn = widget.querySelector('.widget-refresh-btn i');
        const content = widget.querySelector('.widget-content');
        
        // Animate refresh button
        refreshBtn.style.animation = 'spin 1s linear infinite';
        
        // Show loading state
        const originalContent = content.innerHTML;
        content.innerHTML = '<div class="admin-loading"><span class="admin-spinner"></span> Đang cập nhật...</div>';
        
        // Simulate refresh (in real app, this would fetch new data)
        setTimeout(() => {
            content.innerHTML = originalContent;
            refreshBtn.style.animation = 'none';
            
            // Re-setup animations for refreshed content
            this.setupChartAnimations();
            
            // Show success notification
            if (window.adminPanel) {
                window.adminPanel.showNotification('Widget đã được cập nhật', 'success');
            }
        }, 1500);
    }

    setupChartAnimations() {
        // Animate chart bars
        const chartBars = document.querySelectorAll('.bar-fill');
        chartBars.forEach((bar, index) => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-out';
                bar.style.width = targetWidth;
            }, index * 200);
        });

        // Animate revenue counter
        const revenueAmount = document.querySelector('.revenue-amount');
        if (revenueAmount) {
            this.animateCounter(revenueAmount);
        }

        // Animate stat numbers
        const statNumbers = document.querySelectorAll('.stat-content h3');
        statNumbers.forEach(number => {
            this.animateCounter(number);
        });
    }

    animateCounter(element) {
        const text = element.textContent;
        const number = parseInt(text.replace(/[^\d]/g, ''));
        
        if (isNaN(number)) return;
        
        const duration = 2000;
        const steps = 60;
        const increment = number / steps;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= number) {
                current = number;
                clearInterval(timer);
            }
            
            // Format the number based on original text format
            let formattedNumber;
            if (text.includes('.')) {
                formattedNumber = Math.floor(current).toLocaleString('vi-VN');
            } else {
                formattedNumber = Math.floor(current).toString();
            }
            
            // Preserve original formatting
            element.textContent = text.replace(/[\d.,]+/, formattedNumber);
        }, duration / steps);
    }

    setupRefreshFunctionality() {
        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            this.autoRefreshStats();
        }, 300000); // 5 minutes

        // Add manual refresh button to dashboard header
        const dashboardHeader = document.querySelector('.dashboard-header');
        if (dashboardHeader) {
            const refreshAllBtn = document.createElement('button');
            refreshAllBtn.className = 'admin-btn admin-btn-primary';
            refreshAllBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Làm mới';
            refreshAllBtn.style.float = 'right';
            
            refreshAllBtn.addEventListener('click', () => {
                this.refreshAllWidgets();
            });
            
            dashboardHeader.appendChild(refreshAllBtn);
        }
    }

    autoRefreshStats() {
        // In a real application, this would fetch updated statistics
        console.log('Auto-refreshing dashboard stats...');
        
        // Update timestamp
        const now = new Date();
        const timeString = now.toLocaleTimeString('vi-VN');
        
        // Add or update last refresh indicator
        let refreshIndicator = document.querySelector('.last-refresh');
        if (!refreshIndicator) {
            refreshIndicator = document.createElement('small');
            refreshIndicator.className = 'last-refresh';
            refreshIndicator.style.cssText = 'color: #718096; font-size: 12px; float: right; margin-top: 5px;';
            
            const dashboardHeader = document.querySelector('.dashboard-header');
            if (dashboardHeader) {
                dashboardHeader.appendChild(refreshIndicator);
            }
        }
        
        refreshIndicator.textContent = `Cập nhật lần cuối: ${timeString}`;
    }

    refreshAllWidgets() {
        const widgets = document.querySelectorAll('.dashboard-widget');
        widgets.forEach((widget, index) => {
            setTimeout(() => {
                this.refreshWidget(widget);
            }, index * 300);
        });
    }

    setupRealTimeUpdates() {
        // Simulate real-time updates for demo purposes
        // In a real application, this would use WebSockets or Server-Sent Events
        
        setInterval(() => {
            this.simulateActivityUpdate();
        }, 30000); // Every 30 seconds
    }

    simulateActivityUpdate() {
        const activityList = document.querySelector('.activity-list');
        if (!activityList) return;
        
        const activities = [
            {
                type: 'product',
                title: 'Sản phẩm mới được thêm',
                icon: 'fas fa-box'
            },
            {
                type: 'news',
                title: 'Tin tức được cập nhật',
                icon: 'fas fa-newspaper'
            },
            {
                type: 'event',
                title: 'Sự kiện mới được tạo',
                icon: 'fas fa-calendar'
            }
        ];
        
        const randomActivity = activities[Math.floor(Math.random() * activities.length)];
        const now = new Date();
        
        const newItem = document.createElement('li');
        newItem.className = 'activity-item';
        newItem.style.opacity = '0';
        newItem.style.transform = 'translateX(-20px)';
        newItem.innerHTML = `
            <div class="activity-icon">
                <i class="${randomActivity.icon}"></i>
            </div>
            <div class="activity-content">
                <p>${randomActivity.title}</p>
                <small>Vừa xong</small>
            </div>
        `;
        
        // Add to top of list
        activityList.insertBefore(newItem, activityList.firstChild);
        
        // Animate in
        setTimeout(() => {
            newItem.style.transition = 'all 0.3s ease';
            newItem.style.opacity = '1';
            newItem.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove oldest item if more than 5
        const items = activityList.querySelectorAll('.activity-item');
        if (items.length > 5) {
            const lastItem = items[items.length - 1];
            lastItem.style.opacity = '0';
            lastItem.style.transform = 'translateX(20px)';
            setTimeout(() => {
                if (lastItem.parentElement) {
                    lastItem.parentElement.removeChild(lastItem);
                }
            }, 300);
        }
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminDashboard;
}