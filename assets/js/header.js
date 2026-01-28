// Header JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Categories dropdown functionality
    const categoriesDropdown = document.querySelector('.categories-dropdown');
    const categoriesBtn = document.querySelector('.categories-btn');
    const categoriesMenu = document.querySelector('.categories-menu');
    
    if (categoriesDropdown && categoriesBtn && categoriesMenu) {
        // Toggle dropdown on click
        categoriesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isVisible = categoriesMenu.style.opacity === '1';
            
            if (isVisible) {
                categoriesMenu.style.opacity = '0';
                categoriesMenu.style.visibility = 'hidden';
                categoriesMenu.style.transform = 'translateY(-10px)';
            } else {
                categoriesMenu.style.opacity = '1';
                categoriesMenu.style.visibility = 'visible';
                categoriesMenu.style.transform = 'translateY(0px)';
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!categoriesDropdown.contains(e.target)) {
                categoriesMenu.style.opacity = '0';
                categoriesMenu.style.visibility = 'hidden';
                categoriesMenu.style.transform = 'translateY(-10px)';
            }
        });
    }
    
    // Main menu dropdown functionality
    const dropdownItems = document.querySelectorAll('.has-dropdown');
    
    dropdownItems.forEach(function(item) {
        const dropdownMenu = item.querySelector('.dropdown-menu');
        
        if (dropdownMenu) {
            item.addEventListener('mouseenter', function() {
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.visibility = 'visible';
                dropdownMenu.style.transform = 'translateY(0)';
            });
            
            item.addEventListener('mouseleave', function() {
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            });
        }
    });
    
    // Search functionality
    const searchForm = document.querySelector('.search-bar form');
    const searchInput = document.querySelector('.search-input');
    
    if (searchForm && searchInput) {
        searchForm.addEventListener('submit', function(e) {
            if (searchInput.value.trim() === '') {
                e.preventDefault();
                searchInput.focus();
            }
        });
        
        // Search input focus effects
        searchInput.addEventListener('focus', function() {
            // Không thêm box-shadow để tránh vạch xanh
        });
        
        searchInput.addEventListener('blur', function() {
            // Không cần xử lý gì
        });
    }
    
    // Mobile menu functionality (for future mobile implementation)
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Header scroll effect (disabled - remove floating effect)
    let lastScrollTop = 0;
    const header = document.querySelector('.main-header');
    const nav = document.querySelector('.main-nav');
    
    // Commented out to disable floating navbar effect
    /*
    if (header && nav) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add shadow when scrolling
            if (scrollTop > 0) {
                header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                nav.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = 'none';
                nav.style.boxShadow = 'none';
            }
            
            lastScrollTop = scrollTop;
        });
    }
    */
    
    // Active menu item highlighting
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.main-menu a');
    
    menuLinks.forEach(function(link) {
        // Remove active class first
        link.parentElement.classList.remove('active');
        
        try {
            const linkHref = link.getAttribute('href');
            
            // Skip if href is # or empty
            if (!linkHref || linkHref === '#') {
                return;
            }
            
            // For relative URLs, compare directly
            if (linkHref.startsWith('/')) {
                if (linkHref === currentPath) {
                    link.parentElement.classList.add('active');
                } else if (currentPath !== '/' && linkHref !== '/' && currentPath.startsWith(linkHref + '/')) {
                    link.parentElement.classList.add('active');
                }
            } else {
                // For absolute URLs, use URL constructor
                const linkPath = new URL(link.href).pathname;
                if (linkPath === currentPath) {
                    link.parentElement.classList.add('active');
                } else if (currentPath !== '/' && linkPath !== '/' && currentPath.startsWith(linkPath + '/')) {
                    link.parentElement.classList.add('active');
                }
            }
        } catch (e) {
            // Skip invalid URLs
            console.warn('Invalid URL in menu:', link.href);
        }
    });
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn-get-started, .btn-login');
    
    buttons.forEach(function(button) {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close all dropdowns on Escape
            const openDropdowns = document.querySelectorAll('.categories-menu, .dropdown-menu');
            openDropdowns.forEach(function(dropdown) {
                dropdown.style.opacity = '0';
                dropdown.style.visibility = 'hidden';
                dropdown.style.transform = 'translateY(-10px)';
            });
        }
    });
    
    // Accessibility improvements
    const focusableElements = document.querySelectorAll('a, button, input, [tabindex]');
    
    focusableElements.forEach(function(element) {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid #356DF1';
            this.style.outlineOffset = '2px';
        });
        
        element.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
});

// Utility functions
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

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        debounce
    };
}