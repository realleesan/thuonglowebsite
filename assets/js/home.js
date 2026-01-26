// Home Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Hero Section Animation
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        // Add fade-in animation for hero content
        const heroContent = heroSection.querySelector('.hero-content');
        if (heroContent) {
            heroContent.style.opacity = '0';
            heroContent.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                heroContent.style.transition = 'all 0.8s ease';
                heroContent.style.opacity = '1';
                heroContent.style.transform = 'translateY(0)';
            }, 100);
        }
    }
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
});

// Utility function for lazy loading images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading if supported
if ('IntersectionObserver' in window) {
    lazyLoadImages();
}
// Popular Courses Slider Functionality
function initPopularCoursesSlider() {
    const slider = document.querySelector('.courses-slider');
    if (!slider) return;
    
    const coursesGrid = slider.querySelector('.courses-grid');
    const prevBtn = slider.querySelector('.slider-nav-prev');
    const nextBtn = slider.querySelector('.slider-nav-next');
    const bullets = document.querySelectorAll('.pagination-bullet');
    
    if (!coursesGrid || !prevBtn || !nextBtn) return;
    
    let currentIndex = 0;
    const maxIndex = 4; // 5 positions: 0,1,2,3,4
    
    function updateSlider() {
        // Simple percentage translation: each step = 8.33% (100% / 12 slots)
        const translatePercent = -(currentIndex * 8.33);
        coursesGrid.style.transform = `translateX(${translatePercent}%)`;
        
        // Update pagination bullets
        bullets.forEach((bullet, index) => {
            bullet.classList.toggle('active', index === currentIndex);
        });
        
        // Update button states
        prevBtn.style.opacity = currentIndex === 0 ? '0.3' : '1';
        nextBtn.style.opacity = currentIndex >= maxIndex ? '0.3' : '1';
        prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
        nextBtn.style.pointerEvents = currentIndex >= maxIndex ? 'none' : 'auto';
    }
    
    function nextSlide() {
        if (currentIndex < maxIndex) {
            currentIndex++;
            updateSlider();
        }
    }
    
    function prevSlide() {
        if (currentIndex > 0) {
            currentIndex--;
            updateSlider();
        }
    }
    
    function goToSlide(slideIndex) {
        if (slideIndex >= 0 && slideIndex <= maxIndex) {
            currentIndex = slideIndex;
            updateSlider();
        }
    }
    
    // Event listeners
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    bullets.forEach((bullet, index) => {
        bullet.addEventListener('click', () => goToSlide(index));
    });
    
    // Initialize
    updateSlider();
}

// Course Item Animations
function initCourseAnimations() {
    const courseItems = document.querySelectorAll('.course-item');
    
    // Intersection Observer for fade-in animation
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const courseObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    courseItems.forEach((item, index) => {
        // Initial state
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        
        // Observe for animation
        courseObserver.observe(item);
    });
}

// Update DOMContentLoaded event
document.addEventListener('DOMContentLoaded', function() {
    
    // Hero Section Animation
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        // Add fade-in animation for hero content
        const heroContent = heroSection.querySelector('.hero-content');
        if (heroContent) {
            heroContent.style.opacity = '0';
            heroContent.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                heroContent.style.transition = 'all 0.8s ease';
                heroContent.style.opacity = '1';
                heroContent.style.transform = 'translateY(0)';
            }, 100);
        }
    }
    
    // Initialize Popular Courses Slider
    initPopularCoursesSlider();
    
    // Initialize Course Animations
    initCourseAnimations();
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-start-learning');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
});