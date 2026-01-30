// Products Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize products page functionality
    initializeProductsPage();
});

function initializeProductsPage() {
    // Initialize filter toggle
    initializeFilterToggle();
    
    // Initialize sort functionality
    initializeSortFunctionality();
    
    // Initialize filter functionality
    initializeFilterFunctionality();
    
    // Initialize pagination
    initializePagination();
    
    // Initialize responsive behavior
    initializeResponsiveBehavior();
}

// Filter Toggle Functionality
function initializeFilterToggle() {
    const filterToggleBtn = document.getElementById('filterToggle');
    const sidebar = document.getElementById('productsSidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    
    if (filterToggleBtn && sidebar) {
        // Create overlay for mobile
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
        
        filterToggleBtn.addEventListener('click', function() {
            toggleSidebar(sidebar, overlay);
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            closeSidebar(sidebar, overlay);
        });
        
        // Close sidebar with close button
        if (sidebarClose) {
            sidebarClose.addEventListener('click', function() {
                closeSidebar(sidebar, overlay);
            });
        }
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
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

// Sort Functionality
function initializeSortFunctionality() {
    const sortSelect = document.querySelector('.sort-select');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            console.log('Sort by:', selectedValue);
            
            // Add loading state
            showLoadingState();
            
            // Simulate API call or form submission
            setTimeout(() => {
                // Here you would typically make an AJAX request
                // or submit the form to update the products
                hideLoadingState();
                
                // For demo purposes, just log the action
                console.log('Products sorted by:', selectedValue);
            }, 500);
        });
    }
}

// Filter Functionality
function initializeFilterFunctionality() {
    const resetBtn = document.querySelector('.reset-filters-btn');
    const applyBtn = document.querySelector('.apply-filters-btn');
    const filterLinks = document.querySelectorAll('.category-list a, .author-list a, .price-list a, .course-category-list a');
    
    // Reset filters
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            resetAllFilters();
        });
    }
    
    // Apply filters
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    // Filter link clicks
    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            toggleFilterSelection(this);
        });
    });
}

function resetAllFilters() {
    // Remove active states from all filter links
    const activeFilters = document.querySelectorAll('.filter-active');
    activeFilters.forEach(filter => {
        filter.classList.remove('filter-active');
    });
    
    // Reset sort dropdown
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        sortSelect.value = 'post_date';
    }
    
    console.log('All filters reset');
    
    // Simulate page reload or AJAX call
    showLoadingState();
    setTimeout(() => {
        hideLoadingState();
        updateResultsCount('Showing 1-12 of 20 results');
    }, 500);
}

function applyFilters() {
    const activeFilters = document.querySelectorAll('.filter-active');
    const filterData = [];
    
    activeFilters.forEach(filter => {
        filterData.push({
            type: getFilterType(filter),
            value: filter.textContent.trim()
        });
    });
    
    console.log('Applying filters:', filterData);
    
    // Show loading state
    showLoadingState();
    
    // Simulate API call
    setTimeout(() => {
        hideLoadingState();
        
        // Update results count based on filters
        const resultCount = Math.max(1, 20 - filterData.length * 3);
        updateResultsCount(`Showing 1-${Math.min(12, resultCount)} of ${resultCount} results`);
        
        // Close sidebar on mobile after applying filters
        if (window.innerWidth <= 1024) {
            const sidebar = document.getElementById('productsSidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            closeSidebar(sidebar, overlay);
        }
    }, 800);
}

function toggleFilterSelection(filterLink) {
    filterLink.classList.toggle('filter-active');
    
    // Add visual feedback
    if (filterLink.classList.contains('filter-active')) {
        filterLink.style.color = '#356df1';
        filterLink.style.fontWeight = '600';
    } else {
        filterLink.style.color = '';
        filterLink.style.fontWeight = '';
    }
}

function getFilterType(filterElement) {
    const parent = filterElement.closest('.filter-section');
    const title = parent.querySelector('.filter-title').textContent.toLowerCase();
    return title;
}

// Pagination Functionality
function initializePagination() {
    const pageLinks = document.querySelectorAll('.page-link');
    
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.classList.contains('active')) {
                return;
            }
            
            // Remove active state from all links
            pageLinks.forEach(l => l.classList.remove('active'));
            
            // Add active state to clicked link (if it's not prev/next)
            if (!this.classList.contains('prev') && !this.classList.contains('next')) {
                this.classList.add('active');
            }
            
            const pageNumber = this.textContent.trim();
            console.log('Navigate to page:', pageNumber);
            
            // Show loading state
            showLoadingState();
            
            // Simulate page load
            setTimeout(() => {
                hideLoadingState();
                scrollToTop();
            }, 500);
        });
    });
}

// Responsive Behavior
function initializeResponsiveBehavior() {
    let resizeTimer;
    
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            handleResize();
        }, 250);
    });
    
    // Initial check
    handleResize();
}

function handleResize() {
    const sidebar = document.getElementById('productsSidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth > 1024) {
        // Desktop view - ensure sidebar is visible and overlay is hidden
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
        document.body.classList.remove('sidebar-open');
    }
}

// Utility Functions
function showLoadingState() {
    const productsGrid = document.querySelector('.products-grid');
    if (productsGrid) {
        productsGrid.style.opacity = '0.6';
        productsGrid.style.pointerEvents = 'none';
    }
    
    // Add loading spinner if needed
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
    const productsGrid = document.querySelector('.products-grid');
    if (productsGrid) {
        productsGrid.style.opacity = '';
        productsGrid.style.pointerEvents = '';
    }
    
    const loadingSpinner = document.querySelector('.loading-spinner');
    if (loadingSpinner) {
        loadingSpinner.remove();
    }
}

function updateResultsCount(text) {
    const resultsCount = document.querySelector('.results-count span');
    if (resultsCount) {
        resultsCount.textContent = text;
    }
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Course Item Interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to course items
    const courseItems = document.querySelectorAll('.course-item');
    
    courseItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
    
    // Track course clicks for analytics
    const courseLinks = document.querySelectorAll('.course-title a, .btn-start-learning');
    
    courseLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const courseTitle = this.closest('.course-item').querySelector('.course-title a').textContent.trim();
            console.log('Course clicked:', courseTitle);
            
            // Here you could send analytics data
            // trackCourseClick(courseTitle);
        });
    });
});

// Search functionality (if search input exists)
function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput) {
        let searchTimer;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            const query = this.value.trim();
            
            searchTimer = setTimeout(() => {
                if (query.length >= 2) {
                    performSearch(query);
                } else if (query.length === 0) {
                    clearSearch();
                }
            }, 300);
        });
    }
}

function performSearch(query) {
    console.log('Searching for:', query);
    showLoadingState();
    
    // Simulate search API call
    setTimeout(() => {
        hideLoadingState();
        updateResultsCount(`Showing search results for "${query}"`);
    }, 600);
}

function clearSearch() {
    console.log('Clearing search');
    updateResultsCount('Showing 1-12 of 20 results');
}

// Initialize search if needed
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
});

// Export functions for external use if needed
window.ProductsPage = {
    showLoadingState,
    hideLoadingState,
    updateResultsCount,
    resetAllFilters,
    applyFilters
};