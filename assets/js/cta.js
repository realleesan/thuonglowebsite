// CTA Section JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // CTA button functionality
    const ctaButton = document.querySelector('.cta-button');
    
    if (ctaButton) {
        // Button hover effects
        ctaButton.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 15px rgba(53, 109, 241, 0.3)';
        });
        
        ctaButton.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
        
        // Button click tracking (optional)
        ctaButton.addEventListener('click', function(e) {
            // Add analytics tracking here if needed
            console.log('CTA button clicked');
            
            // Optional: Add loading state
            const originalText = this.textContent;
            this.textContent = 'Loading...';
            this.style.pointerEvents = 'none';
            
            // Reset after a short delay (remove this in production)
            setTimeout(() => {
                this.textContent = originalText;
                this.style.pointerEvents = 'auto';
            }, 1000);
        });
    }
    
    // Intersection Observer for animation on scroll
    const ctaSection = document.querySelector('.cta-section');
    
    if (ctaSection && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        observer.observe(ctaSection);
    }
    
    // Smooth scroll for anchor links in CTA
    const ctaLinks = document.querySelectorAll('.cta-section a[href^="#"]');
    
    ctaLinks.forEach(function(link) {
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
    
    // Accessibility improvements
    const focusableElements = document.querySelectorAll('.cta-section a, .cta-section button');
    
    focusableElements.forEach(function(element) {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid #356DF1';
            this.style.outlineOffset = '2px';
        });
        
        element.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
    
    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            const focusedElement = document.activeElement;
            if (focusedElement && focusedElement.classList.contains('cta-button')) {
                e.preventDefault();
                focusedElement.click();
            }
        }
    });
});

// Animation styles (add to CSS if needed)
const animationStyles = `
    .cta-section {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease;
    }
    
    .cta-section.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
`;

// Inject animation styles
if (typeof document !== 'undefined') {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = animationStyles;
    document.head.appendChild(styleSheet);
}

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