// Categories Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize categories page functionality
    initializeCategoriesPage();
});

function initializeCategoriesPage() {
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
    
    // Initialize category interactions
    initializeCategoryInteractions();
}

// Filter Toggle Functionality
function initializeFilterToggle() {
    const filterToggleBtn = document.getElementById('filterToggle');
    const sidebar = document.getElementById('categoriesSidebar');
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
            console.log('Sort categories by:', selectedValue);
            
            // Add loading state
            showLoadingState();
            
            // Simulate API call or form submission
            setTimeout(() => {
                // Here you would typically make an AJAX request
                // or submit the form to update the categories
                hideLoadingState();
                
                // Sort categories based on selection
                sortCategories(selectedValue);
                
                console.log('Categories sorted by:', selectedValue);
            }, 500);
        });
    }
}

function sortCategories(sortBy) {
    const categoriesGrid = document.querySelector('.categories-grid');
    const categoryItems = Array.from(categoriesGrid.querySelectorAll('.category-item'));
    
    categoryItems.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                const nameA = a.querySelector('.category-title a').textContent.trim();
                const nameB = b.querySelector('.category-title a').textContent.trim();
                return nameA.localeCompare(nameB);
                
            case 'name_desc':
                const nameDescA = a.querySelector('.category-title a').textContent.trim();
                const nameDescB = b.querySelector('.category-title a').textContent.trim();
                return nameDescB.localeCompare(nameDescA);
                
            case 'course_count':
                const countA = parseInt(a.querySelector('.course-count span').textContent.match(/\d+/)[0]);
                const countB = parseInt(b.querySelector('.course-count span').textContent.match(/\d+/)[0]);
                return countB - countA;
                
            case 'course_count_desc':
                const countDescA = parseInt(a.querySelector('.course-count span').textContent.match(/\d+/)[0]);
                const countDescB = parseInt(b.querySelector('.course-count span').textContent.match(/\d+/)[0]);
                return countDescA - countDescB;
                
            case 'popular':
                const popularityA = a.querySelector('.category-popularity span').textContent.trim();
                const popularityB = b.querySelector('.category-popularity span').textContent.trim();
                const popularityOrder = ['Hot', 'Popular', 'Trending', 'New', 'Growing'];
                return popularityOrder.indexOf(popularityA) - popularityOrder.indexOf(popularityB);
                
            default:
                return 0;
        }
    });
    
    // Clear and re-append sorted items
    categoriesGrid.innerHTML = '';
    categoryItems.forEach(item => categoriesGrid.appendChild(item));
    
    // Re-initialize category interactions for sorted items
    initializeCategoryInteractions();
}

// Filter Functionality
function initializeFilterFunctionality() {
    const resetBtn = document.querySelector('.reset-filters-btn');
    const applyBtn = document.querySelector('.apply-filters-btn');
    const filterLinks = document.querySelectorAll('.category-type-list a, .course-count-list a, .difficulty-list a');
    
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
        filter.style.color = '';
        filter.style.fontWeight = '';
    });
    
    // Reset sort dropdown
    const sortSelect = document.querySelector('.sort-select');
    if (sortSelect) {
        sortSelect.value = 'name';
    }
    
    // Show all categories
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.style.display = 'block';
    });
    
    console.log('All filters reset');
    
    // Simulate page reload or AJAX call
    showLoadingState();
    setTimeout(() => {
        hideLoadingState();
        updateResultsCount('Showing 1-12 of 15 categories');
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
        
        // Filter categories based on active filters
        filterCategories(filterData);
        
        // Close sidebar on mobile after applying filters
        if (window.innerWidth <= 1024) {
            const sidebar = document.getElementById('categoriesSidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            closeSidebar(sidebar, overlay);
        }
    }, 800);
}

function filterCategories(filters) {
    const categoryItems = document.querySelectorAll('.category-item');
    let visibleCount = 0;
    
    categoryItems.forEach(item => {
        let shouldShow = true;
        
        filters.forEach(filter => {
            const categoryTitle = item.querySelector('.category-title a').textContent.trim();
            const courseCount = parseInt(item.querySelector('.course-count span').textContent.match(/\d+/)[0]);
            const popularity = item.querySelector('.category-popularity span').textContent.trim();
            
            switch (filter.type) {
                case 'category type':
                    if (filter.value.includes('Popular') && !['Popular', 'Hot'].includes(popularity)) {
                        shouldShow = false;
                    }
                    if (filter.value.includes('Trending') && popularity !== 'Trending') {
                        shouldShow = false;
                    }
                    if (filter.value.includes('New') && !['New', 'Growing'].includes(popularity)) {
                        shouldShow = false;
                    }
                    if (filter.value.includes('Hot') && popularity !== 'Hot') {
                        shouldShow = false;
                    }
                    break;
                    
                case 'course count':
                    if (filter.value.includes('10+') && courseCount < 10) {
                        shouldShow = false;
                    }
                    if (filter.value.includes('20+') && courseCount < 20) {
                        shouldShow = false;
                    }
                    if (filter.value.includes('30+') && courseCount < 30) {
                        shouldShow = false;
                    }
                    break;
                    
                case 'difficulty level':
                    // This would typically be based on category metadata
                    // For demo purposes, we'll use category names as indicators
                    if (filter.value.includes('Beginner') && !categoryTitle.toLowerCase().includes('language')) {
                        shouldShow = false;
                    }
                    break;
            }
        });
        
        if (shouldShow) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Update results count
    updateResultsCount(`Showing 1-${Math.min(12, visibleCount)} of ${visibleCount} categories`);
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

// Category Interactions
function initializeCategoryInteractions() {
    const categoryItems = document.querySelectorAll('.category-item');
    
    categoryItems.forEach(item => {
        // Remove existing event listeners to prevent duplicates
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);
    });
    
    // Re-select items after cloning
    const newCategoryItems = document.querySelectorAll('.category-item');
    
    newCategoryItems.forEach(item => {
        // Hover effects
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
        
        // Click tracking
        const categoryLinks = item.querySelectorAll('.category-title a, .category-image a');
        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const categoryTitle = item.querySelector('.category-title a').textContent.trim();
                console.log('Category clicked:', categoryTitle);
                
                // Here you could send analytics data
                // trackCategoryClick(categoryTitle);
            });
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
    const sidebar = document.getElementById('categoriesSidebar');
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
    const categoriesGrid = document.querySelector('.categories-grid');
    if (categoriesGrid) {
        categoriesGrid.classList.add('loading');
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
    const categoriesGrid = document.querySelector('.categories-grid');
    if (categoriesGrid) {
        categoriesGrid.classList.remove('loading');
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
    console.log('Searching categories for:', query);
    showLoadingState();
    
    const categoryItems = document.querySelectorAll('.category-item');
    let visibleCount = 0;
    
    categoryItems.forEach(item => {
        const title = item.querySelector('.category-title a').textContent.toLowerCase();
        const description = item.querySelector('.category-description').textContent.toLowerCase();
        
        if (title.includes(query.toLowerCase()) || description.includes(query.toLowerCase())) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    setTimeout(() => {
        hideLoadingState();
        updateResultsCount(`Showing search results for "${query}" - ${visibleCount} categories found`);
    }, 600);
}

function clearSearch() {
    console.log('Clearing search');
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.style.display = 'block';
    });
    updateResultsCount('Showing 1-12 of 15 categories');
}

// Initialize search if needed
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
});

// Export functions for external use if needed
window.CategoriesPage = {
    showLoadingState,
    hideLoadingState,
    updateResultsCount,
    resetAllFilters,
    applyFilters,
    sortCategories,
    filterCategories
};

// Category Analytics (optional)
function trackCategoryClick(categoryName) {
    // This function can be used to send analytics data
    console.log('Analytics: Category clicked -', categoryName);
    
    // Example: Send to Google Analytics
    // gtag('event', 'category_click', {
    //     'category_name': categoryName,
    //     'page_location': window.location.href
    // });
}

// Category Recommendations (optional)
function showRelatedCategories(currentCategory) {
    // This function could show related categories based on the current one
    console.log('Show related categories for:', currentCategory);
    
    // Implementation would depend on your recommendation logic
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Categories page initialized');
});