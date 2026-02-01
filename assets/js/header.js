// Header JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Categories dropdown functionality
    const categoriesDropdown = document.querySelector('.categories-dropdown');
    const categoriesBtn = document.querySelector('.categories-btn');
    const categoriesMenu = document.querySelector('.categories-menu');
    
    if (categoriesDropdown && categoriesBtn && categoriesMenu) {
        // Show dropdown on hover
        categoriesDropdown.addEventListener('mouseenter', function() {
            categoriesMenu.style.opacity = '1';
            categoriesMenu.style.visibility = 'visible';
            categoriesMenu.style.transform = 'translateY(0px)';
        });
        
        // Hide dropdown on mouse leave
        categoriesDropdown.addEventListener('mouseleave', function() {
            categoriesMenu.style.opacity = '0';
            categoriesMenu.style.visibility = 'hidden';
            categoriesMenu.style.transform = 'translateY(-10px)';
        });
        
        // Handle click on categories button - allow navigation to categories page
        categoriesBtn.addEventListener('click', function(e) {
            // Allow normal navigation to categories page
            // Dropdown will show on hover anyway
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
        const dropdownBtn = item.querySelector('.dropdown-btn');
        
        if (dropdownMenu) {
            // Show dropdown on hover
            item.addEventListener('mouseenter', function() {
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.visibility = 'visible';
                dropdownMenu.style.transform = 'translateY(0)';
            });
            
            // Hide dropdown on mouse leave
            item.addEventListener('mouseleave', function() {
                dropdownMenu.style.opacity = '0';
                dropdownMenu.style.visibility = 'hidden';
                dropdownMenu.style.transform = 'translateY(-10px)';
            });
            
            // Handle click on dropdown button - toggle dropdown
            if (dropdownBtn) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isVisible = dropdownMenu.style.opacity === '1';
                    
                    if (isVisible) {
                        dropdownMenu.style.opacity = '0';
                        dropdownMenu.style.visibility = 'hidden';
                        dropdownMenu.style.transform = 'translateY(-10px)';
                    } else {
                        dropdownMenu.style.opacity = '1';
                        dropdownMenu.style.visibility = 'visible';
                        dropdownMenu.style.transform = 'translateY(0px)';
                    }
                });
            }
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
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'home';
    const menuLinks = document.querySelectorAll('.main-menu a');
    const menuButtons = document.querySelectorAll('.main-menu .dropdown-btn');
    
    // Define page groups for dropdown menus (same as PHP)
    const guidePages = ['about', 'guide', 'contact', 'faq'];
    const newsPages = ['news'];
    const productPages = ['products', 'details', 'course-details']; // Removed 'categories' from here
    
    // First, remove all active classes to ensure clean state
    document.querySelectorAll('.main-menu > li').forEach(function(li) {
        li.classList.remove('active');
    });
    
    // Check if current page belongs to any dropdown group
    let isInGuideGroup = guidePages.includes(currentPage);
    let isInNewsGroup = newsPages.includes(currentPage);
    let isInProductGroup = productPages.includes(currentPage);
    
    // Handle regular menu links
    menuLinks.forEach(function(link) {
        try {
            const linkHref = link.getAttribute('href');
            
            // Skip if href is # or empty
            if (!linkHref || linkHref === '#') {
                return;
            }
            
            // Handle home page (root link)
            if (linkHref === './' || linkHref === '/') {
                if (currentPage === 'home') {
                    link.parentElement.classList.add('active');
                }
                return;
            }
            
            // Parse the page parameter from the link
            let linkPage = null;
            if (linkHref.includes('?page=')) {
                const linkUrl = new URL(linkHref, window.location.origin);
                linkPage = linkUrl.searchParams.get('page');
            }
            
            // Direct page match
            if (linkPage && linkPage === currentPage) {
                link.parentElement.classList.add('active');
                return;
            }
            
            // Check for dropdown group matches
            if (linkPage === 'products' && isInProductGroup) {
                link.parentElement.classList.add('active');
            } else if (linkPage === 'news' && isInNewsGroup) {
                link.parentElement.classList.add('active');
            }
            
        } catch (e) {
            // Skip invalid URLs
            console.warn('Invalid URL in menu:', link.href);
        }
    });
    
    // Handle dropdown buttons (like "Hướng dẫn")
    menuButtons.forEach(function(button) {
        const buttonText = button.textContent.trim();
        
        // Check if current page belongs to this dropdown group
        if (buttonText.includes('Hướng dẫn') && isInGuideGroup) {
            button.parentElement.classList.add('active');
        }
    });
    
    // Categories dropdown is handled by PHP class, no need for additional JS
    
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