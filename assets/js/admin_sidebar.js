// Admin Sidebar JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Toggle sidebar collapse/expand
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            // Debug: Log trạng thái sidebar
            console.log('Sidebar collapsed:', sidebar.classList.contains('collapsed'));
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed);
            
            // Update header and breadcrumb positions
            updateLayoutPositions();
            
            // Force logo update
            updateLogoDisplay();
        });
    }
    
    // Update layout positions when sidebar changes
    function updateLayoutPositions() {
        const header = document.querySelector('.admin-header');
        const breadcrumb = document.querySelector('.admin-breadcrumb');
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        if (window.innerWidth > 768) {
            const leftPosition = isCollapsed ? '70px' : '250px';
            if (header) header.style.left = leftPosition;
            if (breadcrumb) breadcrumb.style.marginLeft = leftPosition;
        }
    }
    
    // Update logo display based on sidebar state
    function updateLogoDisplay() {
        const logoFull = document.querySelector('.logo-full');
        const logoMini = document.querySelector('.logo-mini');
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        // Debug: Log trạng thái logo
        console.log('Logo update - Collapsed:', isCollapsed);
        console.log('Logo full element:', logoFull);
        console.log('Logo mini element:', logoMini);
        
        // Force CSS classes to work properly
        if (logoFull && logoMini) {
            if (isCollapsed) {
                logoFull.style.display = 'none';
                logoFull.style.opacity = '0';
                logoFull.style.visibility = 'hidden';
                
                logoMini.style.display = 'block';
                logoMini.style.opacity = '1';
                logoMini.style.visibility = 'visible';
            } else {
                logoFull.style.display = 'block';
                logoFull.style.opacity = '1';
                logoFull.style.visibility = 'visible';
                
                logoMini.style.display = 'none';
                logoMini.style.opacity = '0';
                logoMini.style.visibility = 'hidden';
            }
        }
    }
    
    // Restore sidebar state from localStorage
    const savedState = localStorage.getItem('admin_sidebar_collapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    }
    
    // Update positions and logo after restoring state
    setTimeout(() => {
        updateLayoutPositions();
        updateLogoDisplay();
    }, 100);
    
    // Handle active menu highlighting
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item's parent
            this.closest('.nav-item').classList.add('active');
        });
    });
    
    // Mobile sidebar toggle
    function handleMobileToggle() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('mobile-hidden');
            
            // Add overlay for mobile
            if (!document.querySelector('.sidebar-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    this.remove();
                });
                document.body.appendChild(overlay);
            }
        } else {
            sidebar.classList.remove('mobile-hidden', 'mobile-open');
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) {
                overlay.remove();
            }
        }
    }
    
    // Mobile sidebar toggle button
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
                
                if (sidebar.classList.contains('mobile-open')) {
                    const overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.5);
                        z-index: 999;
                    `;
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('mobile-open');
                        this.remove();
                    });
                    document.body.appendChild(overlay);
                }
            }
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', handleMobileToggle);
    handleMobileToggle(); // Initial check
    
    // Smooth scrolling for navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state
            this.style.opacity = '0.7';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 200);
        });
    });
    
    // Auto-collapse sidebar on mobile after navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    sidebar.classList.remove('mobile-open');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) {
                        overlay.remove();
                    }
                }, 300);
            }
        });
    });
});