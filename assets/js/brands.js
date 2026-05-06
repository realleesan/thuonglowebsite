/**
 * Brands Page JavaScript - Public
 * Handles interactions for public brands listing page
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initMobileSidebar();
        initLazyLoading();
    });

    /**
     * Mobile Sidebar Toggle
     */
    function initMobileSidebar() {
        const filterToggle = document.getElementById('filterToggle');
        const sidebar = document.getElementById('brandsSidebar');
        const sidebarClose = document.getElementById('sidebarClose');
        
        if (!filterToggle || !sidebar) return;

        // Create overlay if not exists
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        // Toggle sidebar
        filterToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close sidebar
        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }

        overlay.addEventListener('click', closeSidebar);

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });
    }

    /**
     * Lazy Loading Images
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Apply Brand Filters
     */
    window.applyBrandFilters = function() {
        const minProductsRadio = document.querySelector('input[name="min_products"]:checked');
        const minProducts = minProductsRadio ? minProductsRadio.value : '';
        
        let url = '?page=brands';
        
        if (minProducts) {
            url += '&min_products=' + encodeURIComponent(minProducts);
        }
        
        window.location.href = url;
    };

})();
