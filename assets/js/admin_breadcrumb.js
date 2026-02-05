// Admin Breadcrumb JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const breadcrumbLinks = document.querySelectorAll('.breadcrumb-link');
    
    // Add smooth transitions to breadcrumb links
    breadcrumbLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(2px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
        
        // Add loading state on click
        link.addEventListener('click', function() {
            this.style.opacity = '0.7';
            
            setTimeout(() => {
                this.style.opacity = '1';
            }, 200);
        });
    });
    
    // Dynamic breadcrumb generation based on URL
    function updateBreadcrumb() {
        const urlParams = new URLSearchParams(window.location.search);
        const module = urlParams.get('module') || 'dashboard';
        const action = urlParams.get('action') || 'index';
        
        // Module titles mapping
        const moduleMap = {
            'dashboard': { title: 'Dashboard', icon: 'fas fa-tachometer-alt' },
            'products': { title: 'Sản phẩm', icon: 'fas fa-box' },
            'categories': { title: 'Danh mục', icon: 'fas fa-tags' },
            'news': { title: 'Tin tức', icon: 'fas fa-newspaper' },
            'events': { title: 'Sự kiện', icon: 'fas fa-calendar' },
            'orders': { title: 'Đơn hàng', icon: 'fas fa-shopping-cart' },
            'users': { title: 'Người dùng', icon: 'fas fa-users' },
            'affiliates': { title: 'Đại lý', icon: 'fas fa-handshake' },
            'contact': { title: 'Liên hệ', icon: 'fas fa-envelope' },
            'revenue': { title: 'Doanh thu', icon: 'fas fa-chart-line' },
            'settings': { title: 'Cài đặt', icon: 'fas fa-cog' }
        };
        
        // Action titles mapping
        const actionMap = {
            'index': 'Danh sách',
            'add': 'Thêm mới',
            'edit': 'Chỉnh sửa',
            'view': 'Xem chi tiết',
            'delete': 'Xóa'
        };
        
        // Update page title in browser
        const moduleInfo = moduleMap[module] || moduleMap['dashboard'];
        const actionTitle = actionMap[action] || '';
        
        let pageTitle = 'Admin ThuongLo';
        if (module !== 'dashboard') {
            pageTitle = moduleInfo.title + ' - ' + pageTitle;
            if (action !== 'index' && actionTitle) {
                pageTitle = actionTitle + ' - ' + pageTitle;
            }
        }
        
        document.title = pageTitle;
    }
    
    // Update breadcrumb on page load
    updateBreadcrumb();
    
    // Breadcrumb animation on page change
    function animateBreadcrumb() {
        const breadcrumb = document.querySelector('.admin-breadcrumb');
        if (breadcrumb) {
            breadcrumb.style.opacity = '0';
            breadcrumb.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                breadcrumb.style.transition = 'all 0.3s ease';
                breadcrumb.style.opacity = '1';
                breadcrumb.style.transform = 'translateY(0)';
            }, 100);
        }
    }
    
    // Animate breadcrumb on navigation
    breadcrumbLinks.forEach(link => {
        link.addEventListener('click', function() {
            animateBreadcrumb();
        });
    });
    
    // Responsive breadcrumb handling
    function handleResponsiveBreadcrumb() {
        const breadcrumbContent = document.querySelector('.breadcrumb-content');
        const pageTitle = document.querySelector('.page-title');
        
        if (window.innerWidth <= 768) {
            if (breadcrumbContent) {
                breadcrumbContent.style.flexDirection = 'column';
                breadcrumbContent.style.alignItems = 'flex-start';
            }
            if (pageTitle) {
                pageTitle.style.textAlign = 'left';
                pageTitle.style.width = '100%';
            }
        } else {
            if (breadcrumbContent) {
                breadcrumbContent.style.flexDirection = 'row';
                breadcrumbContent.style.alignItems = 'center';
            }
            if (pageTitle) {
                pageTitle.style.textAlign = 'right';
                pageTitle.style.width = 'auto';
            }
        }
    }
    
    window.addEventListener('resize', handleResponsiveBreadcrumb);
    handleResponsiveBreadcrumb(); // Initial check
    
    // Add keyboard navigation for breadcrumb
    breadcrumbLinks.forEach((link, index) => {
        link.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' && index < breadcrumbLinks.length - 1) {
                breadcrumbLinks[index + 1].focus();
            } else if (e.key === 'ArrowLeft' && index > 0) {
                breadcrumbLinks[index - 1].focus();
            }
        });
    });
    
    // Auto-update breadcrumb when URL changes (for SPA-like behavior)
    window.addEventListener('popstate', updateBreadcrumb);
});