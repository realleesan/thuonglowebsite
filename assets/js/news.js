// News Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize news page functionality
    initializeNewsPage();
});

function initializeNewsPage() {
    // Initialize filter functionality
    initializeFilterFunctionality();
    
    // Initialize pagination
    initializePagination();
    
    // Initialize responsive behavior
    initializeResponsiveBehavior();
    
    // Initialize news item interactions
    initializeNewsInteractions();
}

// Filter Functionality
function initializeFilterFunctionality() {
    const resetBtn = document.querySelector('.reset-filters-btn');
    const applyBtn = document.querySelector('.apply-filters-btn');
    const filterLinks = document.querySelectorAll('.category-list a, .links-list a, .tags-cloud a');
    
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
    // Remove active and filter-active states from all filter links
    const activeFilters = document.querySelectorAll('.category-list a, .links-list a, .tags-cloud a');
    activeFilters.forEach(filter => {
        filter.classList.remove('filter-active');
        const filterType = filter.getAttribute('data-filter-type');
        
        // Reset styles based on filter type
        if (filterType === 'tag') {
            filter.style.backgroundColor = '';
            filter.style.color = '';
            filter.style.borderColor = '';
            filter.style.fontWeight = '';
        } else {
            filter.style.color = '';
            filter.style.fontWeight = '';
            filter.style.backgroundColor = '';
        }
    });
    
    console.log('All filters reset');
    
    // Redirect to news page without filters
    window.location.href = '?page=news';
}

function applyFilters() {
    const activeFilters = document.querySelectorAll('.filter-active');
    const filterParams = {
        page: 'news'
    };
    const tags = []; // Array to store multiple tags
    
    activeFilters.forEach(filter => {
        const filterType = filter.getAttribute('data-filter-type');
        const filterValue = filter.getAttribute('data-filter-value');
        
        if (filterValue) {
            // For tags, collect them in an array
            if (filterType === 'tag') {
                tags.push(filterValue);
            } else {
                filterParams[filterType] = filterValue;
            }
        }
    });
    
    console.log('Applying filters:', filterParams, 'Tags:', tags);
    
    // Build URL with filter parameters
    let queryParts = [];
    
    // Add regular parameters
    Object.keys(filterParams).forEach(key => {
        queryParts.push(`${key}=${encodeURIComponent(filterParams[key])}`);
    });
    
    // Add multiple tags as separate parameters
    tags.forEach(tag => {
        queryParts.push(`tag[]=${encodeURIComponent(tag)}`);
    });
    
    const queryString = queryParts.join('&');
    
    // Redirect to filtered page
    window.location.href = '?' + queryString;
}

function toggleFilterSelection(filterLink) {
    const filterType = filterLink.getAttribute('data-filter-type');
    
    // For category and sort, only allow one selection at a time
    if (filterType === 'category' || filterType === 'sort') {
        // Remove filter-active from siblings
        const siblings = filterLink.closest('ul, .tags-cloud').querySelectorAll('a');
        siblings.forEach(sibling => {
            if (sibling !== filterLink) {
                sibling.classList.remove('filter-active');
                sibling.style.color = '';
                sibling.style.fontWeight = '';
                sibling.style.backgroundColor = '';
            }
        });
    }
    // For tags, allow multiple selections (no need to clear siblings)
    
    // Toggle current filter
    filterLink.classList.toggle('filter-active');
    
    // Add visual feedback based on filter type
    if (filterLink.classList.contains('filter-active')) {
        if (filterType === 'tag') {
            // Tags: blue background + white text
            filterLink.style.backgroundColor = '#356df1';
            filterLink.style.color = '#ffffff';
            filterLink.style.borderColor = '#356df1';
            filterLink.style.fontWeight = '500';
        } else {
            // Category & Sort: blue text only
            filterLink.style.color = '#356df1';
            filterLink.style.fontWeight = '600';
            filterLink.style.backgroundColor = 'transparent';
        }
    } else {
        // Reset styles
        if (filterType === 'tag') {
            filterLink.style.backgroundColor = '';
            filterLink.style.color = '';
            filterLink.style.borderColor = '';
            filterLink.style.fontWeight = '';
        } else {
            filterLink.style.color = '';
            filterLink.style.fontWeight = '';
            filterLink.style.backgroundColor = '';
        }
    }
}

// Pagination Functionality
function initializePagination() {
    const pageLinks = document.querySelectorAll('.page-link');
    
    pageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
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
    const sidebar = document.getElementById('newsSidebar');
    
    if (window.innerWidth > 1024) {
        // Desktop view - ensure sidebar is visible
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        document.body.classList.remove('sidebar-open');
    }
}

// News Item Interactions
function initializeNewsInteractions() {
    // Add hover effect for news items
    const newsItems = document.querySelectorAll('.news-item');
    
    newsItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
    
    // Track news clicks for analytics
    const newsLinks = document.querySelectorAll('.news-title a, .read-more');
    
    newsLinks.forEach(link => {
        link.addEventListener('click', function() {
            const newsTitle = this.closest('.news-item').querySelector('.news-title a').textContent.trim();
            console.log('News clicked:', newsTitle);
            
            // Here you could send analytics data
            // trackNewsClick(newsTitle);
        });
    });
    
    // Read more link animation
    const readMoreLinks = document.querySelectorAll('.read-more');
    
    readMoreLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
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
    hideLoadingState,
    resetAllFilters,
    applyFilters
};
