// News Details Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize news details page functionality
    initializeNewsDetailsPage();
});

function initializeNewsDetailsPage() {
    // Initialize smooth scroll
    initializeSmoothScroll();
    
    // Initialize share buttons
    initializeShareButtons();
    
    // Initialize image lazy loading
    initializeImageLazyLoading();
    
    // Initialize related news interactions
    initializeRelatedNews();
}

// Smooth Scroll
function initializeSmoothScroll() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
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
}

// Share Buttons
function initializeShareButtons() {
    const shareButtons = document.querySelectorAll('.share-btn');
    
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            // Open share window
            window.open(url, 'share', 'width=600,height=400,scrollbars=yes');
            
            // Track share event
            console.log('Shared via:', this.classList.contains('facebook') ? 'Facebook' : 
                                      this.classList.contains('twitter') ? 'Twitter' : 'LinkedIn');
        });
    });
}

// Image Lazy Loading
function initializeImageLazyLoading() {
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

// Related News Interactions
function initializeRelatedNews() {
    const relatedItems = document.querySelectorAll('.related-item');
    
    relatedItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Track related news click
            const title = this.querySelector('.related-item-title a').textContent.trim();
            console.log('Related news clicked:', title);
        });
    });
}

// Navigation Buttons
const navButtons = document.querySelectorAll('.nav-prev, .nav-next');

navButtons.forEach(button => {
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});

// Tags
const tags = document.querySelectorAll('.tags-list .tag');

tags.forEach(tag => {
    tag.addEventListener('click', function() {
        console.log('Tag clicked:', this.textContent.trim());
    });
});

// Back to top on page load
window.addEventListener('load', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Export functions for external use
window.NewsDetailsPage = {
    initializeSmoothScroll,
    initializeShareButtons
};
