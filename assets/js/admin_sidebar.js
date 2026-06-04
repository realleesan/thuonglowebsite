// Admin Sidebar JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const navLinks = document.querySelectorAll('.nav-link');

    // Create overlay dynamically for mobile navigation
    let overlay = document.querySelector('.admin-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'admin-overlay';
        document.body.appendChild(overlay);
    }

    // Toggle action based on viewport width
    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');

                // Save state to localStorage
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('admin_sidebar_collapsed', isCollapsed);

                // Update header and breadcrumb positions
                updateLayoutPositions();

                // Force logo update
                updateLogoDisplay();
            } else {
                sidebar.classList.toggle('mobile-open');
                const isOpen = sidebar.classList.contains('mobile-open');

                // Manage overlay and body scroll
                if (isOpen) {
                    overlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                } else {
                    overlay.classList.remove('open');
                    document.body.style.overflow = '';
                }

                // Toggle hamburger icon to times (X) icon
                const icon = sidebarToggleBtn.querySelector('i');
                if (icon) {
                    if (isOpen) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            }
        });
    }

    // Update layout positions when sidebar changes on desktop
    function updateLayoutPositions() {
        const header = document.querySelector('.admin-header');
        const breadcrumb = document.querySelector('.admin-breadcrumb');
        const isCollapsed = sidebar.classList.contains('collapsed');

        if (window.innerWidth > 768) {
            const leftPosition = isCollapsed ? '70px' : '250px';
            if (header) header.style.left = leftPosition;
            if (breadcrumb) breadcrumb.style.marginLeft = leftPosition;
        } else {
            if (header) header.style.left = '0';
            if (breadcrumb) breadcrumb.style.marginLeft = '0';
        }
    }

    // Update logo display based on sidebar state
    function updateLogoDisplay() {
        const logoFull = document.querySelector('.logo-full');
        const logoMini = document.querySelector('.logo-mini');
        const isCollapsed = sidebar.classList.contains('collapsed');

        if (logoFull && logoMini) {
            if (isCollapsed && window.innerWidth > 768) {
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

    // Restore sidebar state from localStorage on desktop
    const savedState = localStorage.getItem('admin_sidebar_collapsed');
    if (savedState === 'true' && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
    }

    // Update positions and logo after restoring state
    setTimeout(() => {
        updateLayoutPositions();
        updateLogoDisplay();
    }, 100);

    // Close mobile sidebar when clicking the overlay
    overlay.addEventListener('click', function () {
        if (sidebar && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';

            const icon = sidebarToggleBtn ? sidebarToggleBtn.querySelector('i') : null;
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });

    // Close mobile sidebar when clicking outside (safe fallback)
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('mobile-open')) {
            if (!sidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target) && !overlay.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';

                const icon = sidebarToggleBtn ? sidebarToggleBtn.querySelector('i') : null;
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        }
    });

    // Handle active menu highlighting
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // Add active class to clicked item's parent
            this.closest('.nav-item').classList.add('active');
        });
    });

    // Handle window resize reset
    window.addEventListener('resize', function () {
        updateLayoutPositions();
        updateLogoDisplay();

        if (window.innerWidth > 768) {
            if (sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';

                const icon = sidebarToggleBtn ? sidebarToggleBtn.querySelector('i') : null;
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        }
    });
});