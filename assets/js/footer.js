// Footer JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Social media links functionality
    const socialLinks = document.querySelectorAll('.thim-social-media a');
    
    socialLinks.forEach(function(link) {
        // Add hover effects
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'all 0.3s ease';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        // Add click tracking (optional)
        link.addEventListener('click', function(e) {
            const platform = this.getAttribute('aria-label');
            console.log('Social media click:', platform);
            // You can add analytics tracking here
        });
    });
    
    // Footer links hover effects
    const footerLinks = document.querySelectorAll('.thim-header-info a, .elementor-element-203b476 a');
    
    footerLinks.forEach(function(link) {
        link.addEventListener('mouseenter', function() {
            this.style.transition = 'color 0.3s ease';
        });
    });
    
    // Contact Sale button functionality
    const contactSaleBtn = document.querySelector('.widget-button');
    
    if (contactSaleBtn) {
        contactSaleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add your contact sale logic here
            console.log('Contact Sale button clicked');
            
            // Example: Open contact modal or redirect to contact page
            // window.location.href = '/contact/';
            
            // Or show a modal
            // showContactModal();
        });
        
        // Button hover effects
        contactSaleBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 12px rgba(8, 38, 69, 0.2)';
        });
        
        contactSaleBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    }
    
    // Logo click functionality
    const footerLogo = document.querySelector('.elementor-element-18fb26a a');
    
    if (footerLogo) {
        footerLogo.addEventListener('click', function(e) {
            // Smooth scroll to top when logo is clicked
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Footer navigation links
    const footerNavLinks = document.querySelectorAll('.thim-header-info a');
    
    footerNavLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Handle placeholder links
            if (href === '#') {
                e.preventDefault();
                console.log('Placeholder link clicked:', this.textContent.trim());
                
                // You can add specific functionality for each link here
                const linkText = this.textContent.trim();
                handleFooterLinkClick(linkText);
            }
        });
    });
    
    // Handle footer link clicks
    function handleFooterLinkClick(linkText) {
        switch(linkText) {
            case 'Design':
                // Redirect to design courses
                console.log('Redirecting to design courses');
                break;
            case 'About us':
                // Redirect to about page
                console.log('Redirecting to about page');
                break;
            case 'Contact us':
                // Redirect to contact page
                console.log('Redirecting to contact page');
                break;
            case 'Privacy Policy':
                // Redirect to privacy policy
                console.log('Redirecting to privacy policy');
                break;
            default:
                console.log('Footer link clicked:', linkText);
        }
    }
    
    // Responsive footer adjustments
    function adjustFooterLayout() {
        const footer = document.querySelector('.site-footer');
        const windowWidth = window.innerWidth;
        
        if (windowWidth <= 767) {
            // Mobile adjustments
            footer.classList.add('mobile-layout');
        } else if (windowWidth <= 1024) {
            // Tablet adjustments
            footer.classList.remove('mobile-layout');
            footer.classList.add('tablet-layout');
        } else {
            // Desktop
            footer.classList.remove('mobile-layout', 'tablet-layout');
        }
    }
    
    // Initial layout adjustment
    adjustFooterLayout();
    
    // Adjust layout on window resize
    window.addEventListener('resize', debounce(adjustFooterLayout, 250));
    
    // Smooth scroll for internal links
    const internalLinks = document.querySelectorAll('a[href^="#"]');
    
    internalLinks.forEach(function(link) {
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
    
    // Footer visibility animation
    function animateFooterOnScroll() {
        const footer = document.querySelector('.site-footer');
        const footerTop = footer.offsetTop;
        const windowHeight = window.innerHeight;
        const scrollTop = window.pageYOffset;
        
        if (scrollTop + windowHeight >= footerTop) {
            footer.classList.add('footer-visible');
        }
    }
    
    // Add CSS class for animation
    const style = document.createElement('style');
    style.textContent = `
        .site-footer {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .site-footer.footer-visible {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);
    
    // Initial check
    animateFooterOnScroll();
    
    // Check on scroll
    window.addEventListener('scroll', debounce(animateFooterOnScroll, 100));
    
    // Accessibility improvements
    const focusableElements = document.querySelectorAll('.site-footer a, .site-footer button');
    
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
            const activeElement = document.activeElement;
            if (activeElement && activeElement.classList.contains('widget-button')) {
                e.preventDefault();
                activeElement.click();
            }
        }
    });
    
    // Copyright year update
    const copyrightText = document.querySelector('.elementor-element-203b476 div div');
    if (copyrightText) {
        const currentYear = new Date().getFullYear();
        copyrightText.innerHTML = copyrightText.innerHTML.replace('2025', currentYear);
    }
    
    // Social media icon loading
    function loadSocialIcons() {
        const socialIcons = document.querySelectorAll('.thim-social-media i');
        
        socialIcons.forEach(function(icon) {
            // Ensure FontAwesome icons are properly loaded
            if (icon.classList.contains('fab')) {
                icon.style.fontFamily = 'FontAwesome';
            }
        });
    }
    
    // Load social icons after fonts are loaded
    if (document.fonts) {
        document.fonts.ready.then(loadSocialIcons);
    } else {
        // Fallback for older browsers
        setTimeout(loadSocialIcons, 1000);
    }
    
    // Footer performance optimization
    function optimizeFooterImages() {
        const footerImages = document.querySelectorAll('.site-footer img');
        
        footerImages.forEach(function(img) {
            // Add lazy loading if not already present
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            
            // Add proper alt text if missing
            if (!img.hasAttribute('alt') || img.getAttribute('alt') === '') {
                img.setAttribute('alt', 'Footer logo');
            }
        });
    }
    
    optimizeFooterImages();
    
    // Error handling for broken links
    const allFooterLinks = document.querySelectorAll('.site-footer a');
    
    allFooterLinks.forEach(function(link) {
        link.addEventListener('error', function() {
            console.warn('Footer link error:', this.href);
        });
    });
    
    // Analytics tracking (optional)
    function trackFooterInteraction(action, element) {
        // Add your analytics tracking code here
        console.log('Footer interaction:', action, element);
        
        // Example for Google Analytics
        // if (typeof gtag !== 'undefined') {
        //     gtag('event', action, {
        //         'event_category': 'Footer',
        //         'event_label': element
        //     });
        // }
    }
    
    // Track footer interactions
    footerLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            trackFooterInteraction('link_click', this.textContent.trim());
        });
    });
    
    if (contactSaleBtn) {
        contactSaleBtn.addEventListener('click', function() {
            trackFooterInteraction('button_click', 'Contact Sale');
        });
    }
    
    socialLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            trackFooterInteraction('social_click', this.getAttribute('aria-label'));
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

// Contact modal functionality (example)
function showContactModal() {
    // Create and show contact modal
    const modal = document.createElement('div');
    modal.className = 'contact-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Contact Sales</h2>
            <p>Get in touch with our sales team for more information.</p>
            <form>
                <input type="text" placeholder="Your Name" required>
                <input type="email" placeholder="Your Email" required>
                <textarea placeholder="Your Message" required></textarea>
                <button type="submit">Send Message</button>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close modal functionality
    const closeBtn = modal.querySelector('.close');
    closeBtn.addEventListener('click', function() {
        document.body.removeChild(modal);
    });
    
    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        debounce,
        showContactModal
    };
}