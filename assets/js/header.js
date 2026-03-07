// Header JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Categories dropdown functionality - Click on arrow to toggle
    const categoriesDropdown = document.querySelector('.categories-dropdown');
    const categoriesBtn = document.querySelector('.categories-btn');
    const categoriesMenu = document.querySelector('.categories-menu');
    const categoriesSvg = categoriesBtn ? categoriesBtn.querySelector('svg') : null;
    
    if (categoriesDropdown && categoriesBtn && categoriesMenu) {
        // Ensure dropdown is hidden on page load
        categoriesMenu.style.opacity = '0';
        categoriesMenu.style.visibility = 'hidden';
        categoriesMenu.style.transform = 'translateY(-10px)';
        categoriesMenu.style.pointerEvents = 'none';
        
        let isOpen = false;
        
        // Add hover handler for categories dropdown
        let categoriesTimeout;
        
        categoriesDropdown.addEventListener('mouseenter', function() {
            clearTimeout(categoriesTimeout);
            categoriesMenu.style.opacity = '1';
            categoriesMenu.style.visibility = 'visible';
            categoriesMenu.style.transform = 'translateY(0px)';
            categoriesMenu.style.pointerEvents = 'auto';
            isOpen = true;
        });
        
        categoriesDropdown.addEventListener('mouseleave', function() {
            categoriesTimeout = setTimeout(function() {
                categoriesMenu.style.opacity = '0';
                categoriesMenu.style.visibility = 'hidden';
                categoriesMenu.style.transform = 'translateY(-10px)';
                categoriesMenu.style.pointerEvents = 'none';
                isOpen = false;
            }, 200); // 200ms delay before hiding
        });
        
        // Function to toggle dropdown
        const toggleCategoriesDropdown = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            isOpen = !isOpen;
            
            if (isOpen) {
                categoriesMenu.style.opacity = '1';
                categoriesMenu.style.visibility = 'visible';
                categoriesMenu.style.transform = 'translateY(0px)';
                categoriesMenu.style.pointerEvents = 'auto';
            } else {
                categoriesMenu.style.opacity = '0';
                categoriesMenu.style.visibility = 'hidden';
                categoriesMenu.style.transform = 'translateY(-10px)';
                categoriesMenu.style.pointerEvents = 'none';
            }
        };
        
        // Click on SVG arrow to toggle
        if (categoriesSvg) {
            categoriesSvg.style.cursor = 'pointer';
            categoriesSvg.addEventListener('click', toggleCategoriesDropdown);
        }
        
        // Click on button text should navigate to categories page
        categoriesBtn.addEventListener('click', function(e) {
            // Only toggle if clicking on SVG
            if (e.target.tagName.toLowerCase() === 'svg' || e.target.tagName.toLowerCase() === 'path') {
                toggleCategoriesDropdown(e);
            }
            // Otherwise allow normal navigation to categories page
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!categoriesDropdown.contains(e.target)) {
                categoriesMenu.style.opacity = '0';
                categoriesMenu.style.visibility = 'hidden';
                categoriesMenu.style.transform = 'translateY(-10px)';
                categoriesMenu.style.pointerEvents = 'none';
                isOpen = false;
            }
        });
    }
    
    // Main menu dropdown functionality - Click on arrow to toggle
    const dropdownItems = document.querySelectorAll('.has-dropdown');
    
    dropdownItems.forEach(function(item) {
        const dropdownMenu = item.querySelector('.dropdown-menu');
        const link = item.querySelector('a');
        const button = item.querySelector('.dropdown-btn');
        const svg = item.querySelector('svg');
        
        if (dropdownMenu) {
            // Ensure dropdown is hidden on page load
            dropdownMenu.style.opacity = '0';
            dropdownMenu.style.visibility = 'hidden';
            dropdownMenu.style.transform = 'translateY(-10px)';
            dropdownMenu.style.pointerEvents = 'none';
            
            // Track dropdown state
            let isOpen = false;
            let dropdownTimeout;
            
            // Add hover handler for dropdown
            item.addEventListener('mouseenter', function() {
                clearTimeout(dropdownTimeout);
                // Close all other dropdowns first
                document.querySelectorAll('.has-dropdown .dropdown-menu').forEach(function(menu) {
                    if (menu !== dropdownMenu) {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                        menu.style.pointerEvents = 'none';
                    }
                });
                
                // Show dropdown on hover
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.visibility = 'visible';
                dropdownMenu.style.transform = 'translateY(0)';
                dropdownMenu.style.pointerEvents = 'auto';
                isOpen = true;
            });
            
            item.addEventListener('mouseleave', function() {
                // Hide dropdown on mouse leave with delay
                dropdownTimeout = setTimeout(function() {
                    dropdownMenu.style.opacity = '0';
                    dropdownMenu.style.visibility = 'hidden';
                    dropdownMenu.style.transform = 'translateY(-10px)';
                    dropdownMenu.style.pointerEvents = 'none';
                    isOpen = false;
                }, 200); // 200ms delay before hiding
            });
            
            // Function to toggle dropdown
            const toggleDropdown = function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns first
                document.querySelectorAll('.has-dropdown .dropdown-menu').forEach(function(menu) {
                    if (menu !== dropdownMenu) {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                        menu.style.pointerEvents = 'none';
                    }
                });
                
                // Toggle current dropdown
                isOpen = !isOpen;
                
                if (isOpen) {
                    dropdownMenu.style.opacity = '1';
                    dropdownMenu.style.visibility = 'visible';
                    dropdownMenu.style.transform = 'translateY(0)';
                    dropdownMenu.style.pointerEvents = 'auto';
                } else {
                    dropdownMenu.style.opacity = '0';
                    dropdownMenu.style.visibility = 'hidden';
                    dropdownMenu.style.transform = 'translateY(-10px)';
                    dropdownMenu.style.pointerEvents = 'none';
                }
            };
            
            // Click on SVG arrow to toggle
            if (svg) {
                svg.style.cursor = 'pointer';
                svg.addEventListener('click', toggleDropdown);
            }
            
            // Click on button to toggle (for "Hướng dẫn" which uses button)
            if (button) {
                button.addEventListener('click', toggleDropdown);
            }
            
            // Click on link text should navigate, not toggle dropdown
            if (link && !button) {
                // Allow normal navigation
                link.addEventListener('click', function(e) {
                    // Only prevent default if clicking on the SVG
                    if (e.target.tagName.toLowerCase() === 'svg' || e.target.tagName.toLowerCase() === 'path') {
                        e.preventDefault();
                        toggleDropdown(e);
                    }
                    // Otherwise allow normal navigation
                });
            }
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const isDropdownClick = e.target.closest('.has-dropdown');
        if (!isDropdownClick) {
            document.querySelectorAll('.has-dropdown .dropdown-menu').forEach(function(menu) {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                menu.style.transform = 'translateY(-10px)';
                menu.style.pointerEvents = 'none';
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
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'home';
    const menuLinks = document.querySelectorAll('.main-menu a');
    const menuButtons = document.querySelectorAll('.main-menu .dropdown-btn');
    
    // Define page groups for dropdown menus (same as PHP)
    const guidePages = ['about', 'guide', 'contact', 'faq'];
    const newsPages = ['news'];
    const productPages = ['products', 'details', 'course-details']; // Removed 'categories' from here
    
    // Note: We don't remove active classes here because PHP already sets them correctly based on current page
    // Just ensure the correct menu item is highlighted based on URL
    
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
            if (linkHref === './' || linkHref === '/' || linkHref === '' || linkHref.endsWith('index.php')) {
                if (currentPage === 'home' || currentPage === '' || window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('/')) {
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