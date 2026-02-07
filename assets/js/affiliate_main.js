/**
 * Affiliate Main JavaScript
 * NO INLINE JS - All interactions handled here
 */

(function() {
    'use strict';

    // ===================================
    // Sidebar Toggle
    // ===================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('affiliateSidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('affiliate_sidebar_collapsed', isCollapsed);
        });

        // Restore sidebar state from localStorage
        const savedState = localStorage.getItem('affiliate_sidebar_collapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
        }
    }

    // ===================================
    // Mobile Sidebar Toggle
    // ===================================
    if (window.innerWidth <= 768) {
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        }
    }

    // ===================================
    // Notifications Dropdown
    // ===================================
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsDropdown = document.querySelector('.notifications-dropdown .dropdown-menu');

    if (notificationsBtn) {
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close user menu if open
            if (userDropdown) {
                userDropdown.classList.remove('show');
            }
            
            // Toggle notifications
            if (notificationsDropdown) {
                notificationsDropdown.classList.toggle('show');
            }
        });
    }

    // ===================================
    // User Menu Dropdown
    // ===================================
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.querySelector('.user-dropdown .dropdown-menu');

    if (userMenuBtn) {
        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close notifications if open
            if (notificationsDropdown) {
                notificationsDropdown.classList.remove('show');
            }
            
            // Toggle user menu
            if (userDropdown) {
                userDropdown.classList.toggle('show');
            }
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notificationsDropdown) {
            notificationsDropdown.classList.remove('show');
        }
        if (userDropdown) {
            userDropdown.classList.remove('show');
        }
    });

    // ===================================
    // Active Menu Highlighting
    // ===================================
    function highlightActiveMenu() {
        const currentUrl = window.location.href;
        const navLinks = document.querySelectorAll('.nav-link');

        navLinks.forEach(link => {
            if (link.href === currentUrl) {
                link.closest('.nav-item').classList.add('active');
            }
        });
    }

    highlightActiveMenu();

    // ===================================
    // Sidebar Submenu Toggle
    // ===================================
    const menuItemsWithSubmenu = document.querySelectorAll('.nav-item.has-submenu');
    
    menuItemsWithSubmenu.forEach(item => {
        const navLink = item.querySelector('.nav-link');
        const submenu = item.querySelector('.submenu');
        
        if (navLink && submenu) {
            // Check if current page is in this submenu
            const submenuLinks = submenu.querySelectorAll('.submenu-link');
            let isCurrentInSubmenu = false;
            
            submenuLinks.forEach(link => {
                if (link.href === window.location.href) {
                    isCurrentInSubmenu = true;
                }
            });
            
            // Open submenu if current page is inside
            if (isCurrentInSubmenu || item.classList.contains('active')) {
                item.classList.add('open');
            }
            
            // Toggle on click
            navLink.addEventListener('click', function(e) {
                // Don't prevent default if sidebar is collapsed (let it navigate)
                if (!sidebar || !sidebar.classList.contains('collapsed')) {
                    e.preventDefault();
                    
                    // Close other submenus
                    menuItemsWithSubmenu.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('open');
                        }
                    });
                    
                    // Toggle current submenu
                    item.classList.toggle('open');
                }
            });
        }
    });

    // ===================================
    // Smooth Scroll for Anchor Links
    // ===================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ===================================
    // Copy to Clipboard Utility
    // ===================================
    window.copyToClipboard = function(text, button) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopySuccess(button);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                fallbackCopyToClipboard(text, button);
            });
        } else {
            fallbackCopyToClipboard(text, button);
        }
    };

    function fallbackCopyToClipboard(text, button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess(button);
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }
        
        document.body.removeChild(textArea);
    }

    function showCopySuccess(button) {
        if (button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Đã sao chép!';
            button.classList.add('btn-success');
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
            }, 2000);
        }
    }

    // ===================================
    // Format Currency
    // ===================================
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    };

    // ===================================
    // Format Number
    // ===================================
    window.formatNumber = function(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    };

    // ===================================
    // Format Date
    // ===================================
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(date);
    };

    // ===================================
    // Show Alert Message
    // ===================================
    window.showAlert = function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        const content = document.querySelector('.affiliate-content');
        if (content) {
            content.insertBefore(alertDiv, content.firstChild);
            
            // Auto dismiss after 5 seconds
            setTimeout(function() {
                alertDiv.remove();
            }, 5000);
        }
    };

    // ===================================
    // Loading Spinner
    // ===================================
    window.showLoading = function() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'loadingSpinner';
        loadingDiv.className = 'loading-spinner';
        loadingDiv.innerHTML = `
            <div class="spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Đang tải...</p>
            </div>
        `;
        document.body.appendChild(loadingDiv);
    };

    window.hideLoading = function() {
        const loadingDiv = document.getElementById('loadingSpinner');
        if (loadingDiv) {
            loadingDiv.remove();
        }
    };

    // ===================================
    // Confirm Dialog
    // ===================================
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    // ===================================
    // Initialize Tooltips (if needed)
    // ===================================
    function initTooltips() {
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(element => {
            element.addEventListener('mouseenter', function() {
                const tooltipText = this.getAttribute('data-tooltip');
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = tooltipText;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
                tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            });
            
            element.addEventListener('mouseleave', function() {
                const tooltip = document.querySelector('.tooltip');
                if (tooltip) {
                    tooltip.remove();
                }
            });
        });
    }

    initTooltips();

    // ===================================
    // Console Log (Development)
    // ===================================
    console.log('Affiliate System Initialized');
    console.log('Design System: Giống Admin');
    console.log('Version: 1.0.0');

})();
