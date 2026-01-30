// Related Courses Section JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if related courses section exists
    if (document.querySelector('.related-courses-section')) {
        initRelatedCourses();
    }
});

function initRelatedCourses() {
    // Initialize course item interactions
    initCourseItemHovers();
    initCourseItemClicks();
    initLazyLoading();
    initSeeMoreLink();
    initScrollAnimation();
}

// Course item hover effects
function initCourseItemHovers() {
    const courseItems = document.querySelectorAll('.course-item');
    
    courseItems.forEach(item => {
        const image = item.querySelector('.course-image');
        const button = item.querySelector('.btn-start-learning');
        
        if (image && button) {
            item.addEventListener('mouseenter', function() {
                // Add subtle animation to the button
                button.style.transform = 'translateY(-1px)';
            });
            
            item.addEventListener('mouseleave', function() {
                // Reset button position
                button.style.transform = 'translateY(0)';
            });
        }
    });
}

// Course item click tracking
function initCourseItemClicks() {
    const courseLinks = document.querySelectorAll('.course-title a, .course-image-link, .btn-start-learning');
    
    courseLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Track course click for analytics (if needed)
            const courseItem = this.closest('.course-item');
            const courseTitle = courseItem.querySelector('.course-title a').textContent.trim();
            
            // You can add analytics tracking here
            console.log('Course clicked:', courseTitle);
            
            // Add loading state to buttons
            if (this.classList.contains('btn-start-learning')) {
                const originalText = this.innerHTML;
                this.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="3" fill="currentColor">
                            <animate attributeName="r" values="3;5;3" dur="1s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="1;0.5;1" dur="1s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    Loading...
                `;
                
                // Reset after a short delay (in case navigation is slow)
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 3000);
            }
        });
    });
}

// Lazy loading for course images
function initLazyLoading() {
    const images = document.querySelectorAll('.course-image');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Add fade-in effect
                    img.style.opacity = '0';
                    img.style.transition = 'opacity 0.3s ease';
                    
                    img.onload = function() {
                        this.style.opacity = '1';
                    };
                    
                    // If image is already loaded
                    if (img.complete) {
                        img.style.opacity = '1';
                    }
                    
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
}

// See More link interaction
function initSeeMoreLink() {
    const seeMoreLink = document.querySelector('.see-more-link');
    
    if (seeMoreLink) {
        seeMoreLink.addEventListener('click', function(e) {
            // Add loading state
            const originalContent = this.innerHTML;
            const svg = this.querySelector('svg');
            
            if (svg) {
                svg.style.transform = 'translateX(4px) rotate(90deg)';
                svg.style.transition = 'transform 0.3s ease';
            }
            
            // Reset after navigation
            setTimeout(() => {
                this.innerHTML = originalContent;
            }, 1000);
        });
    }
}

// Utility function to format price
function formatPrice(price, currency = '$') {
    if (price === 'Free' || price === 'free') {
        return 'Free';
    }
    
    const numPrice = parseFloat(price);
    if (isNaN(numPrice)) {
        return price;
    }
    
    return currency + numPrice.toFixed(2);
}

// Utility function to truncate text
function truncateText(text, maxLength = 100) {
    if (text.length <= maxLength) {
        return text;
    }
    
    return text.substring(0, maxLength).trim() + '...';
}

// Function to dynamically load more courses (if needed for future enhancement)
function loadMoreCourses() {
    // This function can be implemented to load more courses via AJAX
    // For now, it's a placeholder for future enhancement
    console.log('Load more courses functionality can be implemented here');
}

// Function to filter courses by category (if needed for future enhancement)
function filterCoursesByCategory(category) {
    const courseItems = document.querySelectorAll('.course-item');
    
    courseItems.forEach(item => {
        const badge = item.querySelector('.course-category-badge');
        if (badge) {
            const itemCategory = badge.textContent.trim();
            
            if (category === 'all' || itemCategory.toLowerCase() === category.toLowerCase()) {
                item.style.display = 'block';
                item.style.animation = 'fadeIn 0.3s ease';
            } else {
                item.style.display = 'none';
            }
        }
    });
}

// Scroll animation for course items
function initScrollAnimation() {
    const courseItems = document.querySelectorAll('.course-item');
    
    if ('IntersectionObserver' in window) {
        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    // Add staggered animation delay
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                    
                    animationObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        courseItems.forEach(item => {
            // Set initial state
            item.style.opacity = '0';
            item.style.transform = 'translateY(30px)';
            item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            
            animationObserver.observe(item);
        });
    }
}

// Add CSS animation for fade in effect
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);