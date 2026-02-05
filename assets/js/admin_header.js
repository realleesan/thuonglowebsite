// Admin Header JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsMenu = document.getElementById('notificationsMenu');
    const userBtn = document.getElementById('userBtn');
    const userMenu = document.getElementById('userMenu');
    const searchInput = document.querySelector('.search-input');
    
    // Toggle notifications dropdown
    if (notificationsBtn && notificationsMenu) {
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close user menu if open
            if (userMenu) {
                userMenu.classList.remove('show');
            }
            
            // Toggle notifications menu
            notificationsMenu.classList.toggle('show');
        });
    }
    
    // Toggle user dropdown
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Close notifications menu if open
            if (notificationsMenu) {
                notificationsMenu.classList.remove('show');
            }
            
            // Toggle user menu
            userMenu.classList.toggle('show');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationsMenu && !notificationsMenu.contains(e.target) && !notificationsBtn.contains(e.target)) {
            notificationsMenu.classList.remove('show');
        }
        
        if (userMenu && !userMenu.contains(e.target) && !userBtn.contains(e.target)) {
            userMenu.classList.remove('show');
        }
    });
    
    // Close dropdowns on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (notificationsMenu) {
                notificationsMenu.classList.remove('show');
            }
            if (userMenu) {
                userMenu.classList.remove('show');
            }
        }
    });
    
    // Search functionality
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            }
        });
        
        // Search on form submit
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query.length >= 2) {
                    performSearch(query);
                }
            });
        }
    }
    
    // Perform search function
    function performSearch(query) {
        console.log('Searching for:', query);
        
        // Add loading state to search input
        searchInput.classList.add('searching');
        
        // Simulate search (replace with actual search implementation)
        setTimeout(() => {
            searchInput.classList.remove('searching');
            
            // Redirect to search results page
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('search', query);
            window.location.href = currentUrl.toString();
        }, 500);
    }
    
    // Notification badge animation
    const notificationBadge = document.querySelector('.notifications-dropdown .badge');
    if (notificationBadge) {
        // Animate badge on new notification (simulate)
        function animateBadge() {
            notificationBadge.style.transform = 'scale(1.2)';
            setTimeout(() => {
                notificationBadge.style.transform = 'scale(1)';
            }, 200);
        }
        
        // Example: animate badge every 30 seconds (remove in production)
        // setInterval(animateBadge, 30000);
    }
    
    // Auto-hide notifications after reading
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.style.opacity = '0.6';
            this.style.pointerEvents = 'none';
            
            // Mark as read (implement actual API call)
            setTimeout(() => {
                this.remove();
                updateNotificationBadge();
            }, 300);
        });
    });
    
    // Update notification badge count
    function updateNotificationBadge() {
        const remainingNotifications = document.querySelectorAll('.notification-item').length;
        if (notificationBadge) {
            if (remainingNotifications > 0) {
                notificationBadge.textContent = remainingNotifications;
            } else {
                notificationBadge.style.display = 'none';
            }
        }
    }
    
    // Responsive header adjustments
    function handleResponsiveHeader() {
        const header = document.querySelector('.admin-header');
        const breadcrumb = document.querySelector('.admin-breadcrumb');
        const sidebar = document.querySelector('.admin-sidebar');
        
        if (window.innerWidth <= 768) {
            if (header) header.style.left = '0';
            if (breadcrumb) breadcrumb.style.marginLeft = '0';
        } else {
            if (sidebar && sidebar.classList.contains('collapsed')) {
                if (header) header.style.left = '70px';
                if (breadcrumb) breadcrumb.style.marginLeft = '70px';
            } else {
                if (header) header.style.left = '250px';
                if (breadcrumb) breadcrumb.style.marginLeft = '250px';
            }
        }
    }
    
    window.addEventListener('resize', handleResponsiveHeader);
    handleResponsiveHeader(); // Initial check
    
    // Sidebar toggle effect on header
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            setTimeout(handleResponsiveHeader, 300); // Wait for sidebar animation
        });
    }
});