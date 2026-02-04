/**
 * Admin Sidebar - JavaScript
 * Handles sidebar navigation and responsive behavior
 */

class AdminSidebar {
    constructor() {
        this.sidebar = document.querySelector('.admin-sidebar');
        this.toggleButton = document.querySelector('.sidebar-toggle');
        this.overlay = null;
        this.isCollapsed = false;
        this.isMobile = window.innerWidth <= 768;
        
        this.init();
    }

    init() {
        this.createOverlay();
        this.setupEventListeners();
        this.setupResponsive();
        this.highlightActiveMenu();
        this.setupMenuAnimations();
    }

    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'sidebar-overlay';
        document.body.appendChild(this.overlay);
    }

    setupEventListeners() {
        // Toggle button click
        if (this.toggleButton) {
            this.toggleButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
        }

        // Overlay click (mobile)
        this.overlay.addEventListener('click', () => {
            if (this.isMobile) {
                this.hide();
            }
        });

        // Window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });

        // Menu item clicks
        this.setupMenuClicks();

        // Keyboard navigation
        this.setupKeyboardNavigation();
    }

    setupMenuClicks() {
        const menuLinks = this.sidebar.querySelectorAll('.nav-menu a');
        menuLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Remove active class from all links
                menuLinks.forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                link.classList.add('active');
                
                // Store active menu in localStorage
                localStorage.setItem('admin_active_menu', link.getAttribute('href'));
                
                // Hide sidebar on mobile after click
                if (this.isMobile) {
                    setTimeout(() => {
                        this.hide();
                    }, 150);
                }
            });
        });
    }

    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // ESC key to close sidebar on mobile
            if (e.key === 'Escape' && this.isMobile && !this.isCollapsed) {
                this.hide();
            }
            
            // Alt + M to toggle sidebar
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    setupResponsive() {
        this.handleResize();
    }

    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth <= 768;
        
        if (wasMobile !== this.isMobile) {
            if (this.isMobile) {
                // Switching to mobile
                this.hide();
                this.updateContentMargin(false);
            } else {
                // Switching to desktop
                this.show();
                this.updateContentMargin(true);
            }
        }
    }

    toggle() {
        if (this.isCollapsed) {
            this.show();
        } else {
            this.hide();
        }
    }

    show() {
        this.isCollapsed = false;
        this.sidebar.classList.remove('collapsed');
        
        if (this.isMobile) {
            this.sidebar.classList.add('show');
            this.overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        } else {
            this.updateContentMargin(true);
        }
        
        // Trigger animation for menu items
        this.animateMenuItems();
    }

    hide() {
        this.isCollapsed = true;
        
        if (this.isMobile) {
            this.sidebar.classList.remove('show');
            this.overlay.classList.remove('show');
            document.body.style.overflow = '';
        } else {
            this.sidebar.classList.add('collapsed');
            this.updateContentMargin(false);
        }
    }

    updateContentMargin(show) {
        const content = document.querySelector('.admin-content');
        if (content && !this.isMobile) {
            content.style.marginLeft = show ? '250px' : '0';
        }
    }

    highlightActiveMenu() {
        const currentPath = window.location.search;
        const menuLinks = this.sidebar.querySelectorAll('.nav-menu a');
        
        // First, try to get from localStorage
        const storedActiveMenu = localStorage.getItem('admin_active_menu');
        
        menuLinks.forEach(link => {
            link.classList.remove('active');
            
            const linkPath = link.getAttribute('href');
            
            // Check if this link matches current path or stored active menu
            if (linkPath === currentPath || linkPath === storedActiveMenu) {
                link.classList.add('active');
            }
        });
        
        // If no active menu found, highlight dashboard by default
        const activeMenu = this.sidebar.querySelector('.nav-menu a.active');
        if (!activeMenu) {
            const dashboardLink = this.sidebar.querySelector('.nav-menu a[href*="dashboard"]');
            if (dashboardLink) {
                dashboardLink.classList.add('active');
            }
        }
    }

    setupMenuAnimations() {
        const menuItems = this.sidebar.querySelectorAll('.nav-menu li');
        
        // Reset animation
        menuItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
        });
        
        // Animate items
        setTimeout(() => {
            this.animateMenuItems();
        }, 100);
    }

    animateMenuItems() {
        const menuItems = this.sidebar.querySelectorAll('.nav-menu li');
        
        menuItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 50);
        });
    }

    // Method to add notification badges
    addNotificationBadge(menuSelector, count) {
        const menuItem = this.sidebar.querySelector(menuSelector);
        if (menuItem) {
            let badge = menuItem.querySelector('.nav-menu-badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'nav-menu-badge';
                menuItem.appendChild(badge);
            }
            
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Method to remove notification badge
    removeNotificationBadge(menuSelector) {
        const menuItem = this.sidebar.querySelector(menuSelector);
        if (menuItem) {
            const badge = menuItem.querySelector('.nav-menu-badge');
            if (badge) {
                badge.remove();
            }
        }
    }

    // Method to update user info
    updateUserInfo(userData) {
        const userAvatar = this.sidebar.querySelector('.sidebar-user-avatar');
        const userName = this.sidebar.querySelector('.sidebar-user-info h4');
        const userRole = this.sidebar.querySelector('.sidebar-user-info p');
        
        if (userAvatar && userData.name) {
            userAvatar.textContent = userData.name.charAt(0).toUpperCase();
        }
        
        if (userName && userData.name) {
            userName.textContent = userData.name;
        }
        
        if (userRole && userData.role) {
            userRole.textContent = userData.role;
        }
    }

    // Method to add custom menu item
    addMenuItem(menuData) {
        const navMenu = this.sidebar.querySelector('.nav-menu');
        if (navMenu && menuData) {
            const li = document.createElement('li');
            li.innerHTML = `
                <a href="${menuData.href}">
                    <span class="nav-menu-icon">${menuData.icon || '📄'}</span>
                    <span class="nav-menu-text">${menuData.text}</span>
                </a>
            `;
            
            if (menuData.position === 'top') {
                navMenu.insertBefore(li, navMenu.firstChild);
            } else {
                navMenu.appendChild(li);
            }
            
            // Re-setup click handlers
            this.setupMenuClicks();
        }
    }

    // Method to remove menu item
    removeMenuItem(href) {
        const menuItem = this.sidebar.querySelector(`.nav-menu a[href="${href}"]`);
        if (menuItem && menuItem.parentElement) {
            menuItem.parentElement.remove();
        }
    }

    // Method to get current active menu
    getActiveMenu() {
        const activeMenu = this.sidebar.querySelector('.nav-menu a.active');
        return activeMenu ? activeMenu.getAttribute('href') : null;
    }

    // Method to set active menu programmatically
    setActiveMenu(href) {
        const menuLinks = this.sidebar.querySelectorAll('.nav-menu a');
        menuLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === href) {
                link.classList.add('active');
            }
        });
        
        localStorage.setItem('admin_active_menu', href);
    }
}

// Initialize sidebar when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminSidebar = new AdminSidebar();
});

// Utility functions for sidebar management
window.SidebarUtils = {
    // Show notification on menu item
    showMenuNotification: (menuSelector, count) => {
        if (window.adminSidebar) {
            window.adminSidebar.addNotificationBadge(menuSelector, count);
        }
    },
    
    // Hide notification on menu item
    hideMenuNotification: (menuSelector) => {
        if (window.adminSidebar) {
            window.adminSidebar.removeNotificationBadge(menuSelector);
        }
    },
    
    // Update user info in sidebar
    updateUser: (userData) => {
        if (window.adminSidebar) {
            window.adminSidebar.updateUserInfo(userData);
        }
    },
    
    // Add custom menu item
    addMenu: (menuData) => {
        if (window.adminSidebar) {
            window.adminSidebar.addMenuItem(menuData);
        }
    },
    
    // Remove menu item
    removeMenu: (href) => {
        if (window.adminSidebar) {
            window.adminSidebar.removeMenuItem(href);
        }
    },
    
    // Get current active menu
    getActiveMenu: () => {
        return window.adminSidebar ? window.adminSidebar.getActiveMenu() : null;
    },
    
    // Set active menu
    setActiveMenu: (href) => {
        if (window.adminSidebar) {
            window.adminSidebar.setActiveMenu(href);
        }
    }
};