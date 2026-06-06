// Categories Page JavaScript

document.addEventListener('DOMContentLoaded', function () {
    initializeCategoriesPage();
});

function initializeCategoriesPage() {
    // Initialize filter toggle
    initializeFilterToggle();

    // Initialize filter functionality
    initializeFilterFunctionality();

    // Initialize filter accordion
    initializeFilterAccordion();

    // Initialize responsive behavior
    initializeResponsiveBehavior();

    // Initialize lazy loading
    initLazyLoading();

    // Initialize main category accordion items
    initializeAccordionCategories();
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
    const sidebar = document.getElementById('categoriesSidebar');
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
                // Single-select checkbox logic (behaves like radio but is a checkbox)
                if (checkbox.checked && checkbox.classList.contains('filter-checkbox-single')) {
                    const section = this.closest('.filter-section');
                    if (section) {
                        section.querySelectorAll('input[type="checkbox"]').forEach(other => {
                            if (other !== checkbox) {
                                other.checked = false;
                                const otherLi = other.closest('.category-item');
                                if (otherLi) {
                                    otherLi.classList.remove('active');
                                }
                            }
                        });
                    }
                }

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
    const sidebar = document.getElementById('categoriesSidebar');
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

// Lazy loading for images
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

// Main categories list accordion functionality
function initializeAccordionCategories() {
    const accordions = document.querySelectorAll('.category-accordion-item');

    accordions.forEach(acc => {
        const header = acc.querySelector('.category-accordion-header');

        if (header) {
            header.addEventListener('click', function (e) {
                // Ignore click if it originated from a link (like title or image link)
                if (e.target.closest('a')) {
                    return;
                }

                // Toggle active state
                acc.classList.toggle('active');
            });
        }
    });

    // Handle nested subcategories (Level 2, 3, etc.)
    const nodeAccordions = document.querySelectorAll('.category-tree-node');
    nodeAccordions.forEach(node => {
        const header = node.querySelector(':scope > .category-node-header');
        if (header) {
            header.addEventListener('click', function (e) {
                // Ignore click if it originated from a link
                if (e.target.closest('a')) {
                    return;
                }

                // Toggle active state of this node
                node.classList.toggle('active');
                e.stopPropagation(); // Prevent event bubbling to parent node headers
            });
        }
    });
}