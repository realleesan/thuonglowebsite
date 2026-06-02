// News Page JavaScript

document.addEventListener('DOMContentLoaded', function () {
    // Initialize news page functionality
    initializeNewsPage();
});

function initializeNewsPage() {
    // Initialize filter toggle
    initializeFilterToggle();

    // Initialize filter functionality
    initializeFilterFunctionality();

    // Initialize filter accordion
    initializeFilterAccordion();

    // Initialize pagination
    initializePagination();

    // Initialize responsive behavior
    initializeResponsiveBehavior();

    // Initialize news item interactions
    initializeNewsInteractions();
}

// Filter Accordion Functionality
function initializeFilterAccordion() {
    const filterTitles = document.querySelectorAll('.filter-title');

    filterTitles.forEach(title => {
        title.addEventListener('click', function (e) {
            if (window.innerWidth <= 1024) {
                // Toggle active class to show/hide content via CSS
                this.classList.toggle('active');
            }
        });
    });
}

// Filter Toggle Functionality
function initializeFilterToggle() {
    const filterToggleBtn = document.getElementById('filterToggle');
    const sidebar = document.getElementById('newsSidebar');
    const sidebarClose = document.getElementById('sidebarClose');

    if (filterToggleBtn && sidebar) {
        // Create overlay for mobile
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        filterToggleBtn.addEventListener('click', function () {
            toggleSidebar(sidebar, overlay);
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function () {
            closeSidebar(sidebar, overlay);
        });

        // Close sidebar with close button
        if (sidebarClose) {
            sidebarClose.addEventListener('click', function () {
                closeSidebar(sidebar, overlay);
            });
        }

        // Close sidebar on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar(sidebar, overlay);
            }
        });
    }
}

function toggleSidebar(sidebar, overlay) {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.classList.toggle('sidebar-open');
}

function closeSidebar(sidebar, overlay) {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    document.body.classList.remove('sidebar-open');
}

// Filter Functionality
function initializeFilterFunctionality() {
    const filterItems = document.querySelectorAll('.category-item-content');

    filterItems.forEach(item => {
        item.addEventListener('click', function (e) {
            const checkbox = this.querySelector('input[type="checkbox"]');
            const radio = this.querySelector('input[type="radio"]');

            // If we didn't click the input or label directly, toggle/select the input
            if (e.target.tagName !== 'INPUT' && !e.target.closest('label')) {
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                } else if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            // Update active class based on state
            if (checkbox) {
                const li = this.closest('.category-item');
                if (checkbox.checked) {
                    li.classList.add('active');
                } else {
                    li.classList.remove('active');
                }
            } else if (radio) {
                const section = this.closest('.filter-section');
                if (section) {
                    section.querySelectorAll('.category-item').forEach(li => li.classList.remove('active'));
                }
                const li = this.closest('.category-item');
                if (radio.checked) {
                    li.classList.add('active');
                }
            }
        });
    });
}

// Pagination Functionality
function initializePagination() {
    const pageLinks = document.querySelectorAll('.page-link');

    pageLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            if (this.classList.contains('active')) {
                e.preventDefault();
                return;
            }

            // Show loading state
            showLoadingState();

            // Allow normal navigation
            // The page will reload with new pagination
        });
    });
}

// Responsive Behavior
function initializeResponsiveBehavior() {
    let resizeTimer;

    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            handleResize();
        }, 250);
    });

    // Initial check
    handleResize();
}

function handleResize() {
    const sidebar = document.getElementById('newsSidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (window.innerWidth > 1024) {
        // Desktop view - ensure sidebar is visible
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
        document.body.classList.remove('sidebar-open');
    }
}

// News Item Interactions
function initializeNewsInteractions() {
    // Add hover effect for news items
    const newsItems = document.querySelectorAll('.news-item');

    newsItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // Track news clicks for analytics
    const newsLinks = document.querySelectorAll('.news-title a, .read-more');

    newsLinks.forEach(link => {
        link.addEventListener('click', function () {
            const newsTitle = this.closest('.news-item').querySelector('.news-title a').textContent.trim();
            console.log('News clicked:', newsTitle);

            // Here you could send analytics data
            // trackNewsClick(newsTitle);
        });
    });

    // Read more link animation
    const readMoreLinks = document.querySelectorAll('.read-more');

    readMoreLinks.forEach(link => {
        link.addEventListener('mouseenter', function () {
            this.style.transform = 'translateX(5px)';
        });

        link.addEventListener('mouseleave', function () {
            this.style.transform = 'translateX(0)';
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });

        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

// Utility Functions
function showLoadingState() {
    const newsList = document.querySelector('.news-list');
    if (newsList) {
        newsList.style.opacity = '0.6';
        newsList.style.pointerEvents = 'none';
    }

    // Add loading spinner
    const loadingSpinner = document.createElement('div');
    loadingSpinner.className = 'loading-spinner';
    loadingSpinner.innerHTML = '<div class="spinner"></div>';
    loadingSpinner.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    `;

    const spinnerCSS = `
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #356df1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;

    if (!document.querySelector('#spinner-styles')) {
        const style = document.createElement('style');
        style.id = 'spinner-styles';
        style.textContent = spinnerCSS;
        document.head.appendChild(style);
    }

    document.body.appendChild(loadingSpinner);
}

function hideLoadingState() {
    const newsList = document.querySelector('.news-list');
    if (newsList) {
        newsList.style.opacity = '';
        newsList.style.pointerEvents = '';
    }

    const loadingSpinner = document.querySelector('.loading-spinner');
    if (loadingSpinner) {
        loadingSpinner.remove();
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Export functions for external use if needed
window.NewsPage = {
    showLoadingState,
    hideLoadingState
};
