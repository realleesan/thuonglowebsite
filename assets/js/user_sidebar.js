// User Sidebar JavaScript - Simple & Clean
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('userSidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Load user data
    loadUserData();
    
    // Toggle sidebar collapse/expand
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                // Mobile: toggle open/close
                sidebar.classList.toggle('mobile-open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('active');
                }
            } else {
                // Desktop: toggle collapse/expand
                sidebar.classList.toggle('collapsed');
                
                // Save state to localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('user_sidebar_collapsed', isCollapsed);
            }
        });
    }
    
    // Sidebar overlay click handler
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
        });
    }
    
    // Restore sidebar state from localStorage
    const savedState = localStorage.getItem('user_sidebar_collapsed');
    if (savedState === 'true' && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
    }
    
    // Handle active menu highlighting
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked item's parent
            this.closest('.nav-item').classList.add('active');
            
            // Auto-close mobile sidebar after navigation
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    sidebar.classList.remove('mobile-open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }, 300);
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop mode
            sidebar.classList.remove('mobile-open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
        } else {
            // Mobile mode
            sidebar.classList.remove('collapsed');
        }
    });
    
    // Load user data from API and update UI
    async function loadUserData() {
        try {
            const response = await fetch('api.php?action=getUserData');
            const data = await response.json();
            
            // Update user info in sidebar
            const userName = document.getElementById('userName');
            const userLevel = document.getElementById('userLevel');
            const cartCount = document.getElementById('cartCount');
            const wishlistCount = document.getElementById('wishlistCount');
            
            if (userName) userName.textContent = data.user.name;
            if (userLevel) userLevel.textContent = data.user.level + ' Member';
            if (cartCount) cartCount.textContent = data.cart.length;
            if (wishlistCount) wishlistCount.textContent = data.wishlist.length;
            
            // Store data globally for other modules to use
            window.userData = data;
            
        } catch (error) {
            console.error('Error loading user data:', error);
            
            // Fallback data
            window.userData = {
                user: {
                    name: 'Người dùng',
                    level: 'Basic'
                },
                cart: [],
                wishlist: []
            };
        }
    }
});