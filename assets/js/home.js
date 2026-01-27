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
    const bullets = document.querySelectorAll('.pagination-bullet');
    
    if (!coursesGrid) return;
    
    let currentIndex = 0;
    const totalItems = coursesGrid.children.length; // 8 items
    const itemsPerView = 4; // Show 4 items at once
    const maxIndex = totalItems - itemsPerView; // 8-4 = 4, so we have 5 positions (0,1,2,3,4)
    
    // Debug info
    console.log('Total items:', totalItems);
    console.log('Items per view:', itemsPerView);
    console.log('Max index:', maxIndex);
    console.log('Bullets found:', bullets.length);
    
    function updateSlider() {
        // Use fixed values - no more dynamic calculation
        const itemWidth = 270;
        const gap = 15;
        const moveDistance = itemWidth + gap; // 285px per step
        const translateX = -(currentIndex * moveDistance);
        coursesGrid.style.transform = `translateX(${translateX}px)`;
        
        console.log('Current index:', currentIndex, 'TranslateX:', translateX);
        
        // Update pagination bullets
        bullets.forEach((bullet, index) => {
            bullet.classList.toggle('active', index === currentIndex);
        });
    }
    
    function goToSlide(slideIndex) {
        console.log('Going to slide:', slideIndex, 'Max index:', maxIndex);
        // Allow navigation to all bullet positions (0-4)
        if (slideIndex >= 0 && slideIndex <= maxIndex) {
            currentIndex = slideIndex;
            updateSlider();
        }
    }
    
    // Event listeners - only for pagination bullets
    bullets.forEach((bullet, index) => {
        bullet.addEventListener('click', () => {
            console.log('Bullet clicked:', index);
            goToSlide(index);
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', updateSlider);
    
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

// Outstanding Categories Functionality
function initOutstandingCategories() {
    const categoryItems = document.querySelectorAll('.thim-widget-course-categories-grid li');
    
    // Intersection Observer for fade-in animation
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const categoryObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    categoryItems.forEach((item, index) => {
        // Initial state
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        
        // Observe for animation
        categoryObserver.observe(item);
        
        // Add hover effects
        const link = item.querySelector('a');
        if (link) {
            link.addEventListener('mouseenter', function() {
                item.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            link.addEventListener('mouseleave', function() {
                item.style.transform = 'translateY(0) scale(1)';
            });
        }
    });
}

// Update the main DOMContentLoaded event to include Outstanding Categories
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
    
    // Initialize Outstanding Categories
    initOutstandingCategories();
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-start-learning, .widget-button');
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

// New Release Slider Functionality
function initNewReleaseSlider() {
    const slider = document.querySelector('.new-release-section .courses-slider');
    if (!slider) return;
    
    const coursesGrid = slider.querySelector('.courses-grid');
    const bullets = document.querySelectorAll('.new-release-section .pagination-bullet');
    
    if (!coursesGrid) return;
    
    let currentIndex = 0;
    const totalItems = coursesGrid.children.length; // 8 items
    const itemsPerView = 4; // Show 4 items at once
    const maxIndex = totalItems - itemsPerView; // 8-4 = 4, so we have 5 positions (0,1,2,3,4)
    
    // Debug info
    console.log('New Release - Total items:', totalItems);
    console.log('New Release - Items per view:', itemsPerView);
    console.log('New Release - Max index:', maxIndex);
    console.log('New Release - Bullets found:', bullets.length);
    
    function updateSlider() {
        // Use fixed values - no more dynamic calculation
        const itemWidth = 270;
        const gap = 15;
        const moveDistance = itemWidth + gap; // 285px per step
        const translateX = -(currentIndex * moveDistance);
        coursesGrid.style.transform = `translateX(${translateX}px)`;
        
        console.log('New Release - Current index:', currentIndex, 'TranslateX:', translateX);
        
        // Update pagination bullets
        bullets.forEach((bullet, index) => {
            bullet.classList.toggle('active', index === currentIndex);
        });
    }
    
    function goToSlide(slideIndex) {
        console.log('New Release - Going to slide:', slideIndex, 'Max index:', maxIndex);
        // Allow navigation to all bullet positions (0-4)
        if (slideIndex >= 0 && slideIndex <= maxIndex) {
            currentIndex = slideIndex;
            updateSlider();
        }
    }
    
    // Event listeners - only for pagination bullets
    bullets.forEach((bullet, index) => {
        bullet.addEventListener('click', () => {
            console.log('New Release - Bullet clicked:', index);
            goToSlide(index);
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', updateSlider);
    
    // Initialize
    updateSlider();
}

// New Release Course Item Animations
function initNewReleaseCourseAnimations() {
    const courseItems = document.querySelectorAll('.new-release-section .course-item');
    
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

// Update the main DOMContentLoaded event to include New Release
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
    
    // Initialize New Release Slider
    initNewReleaseSlider();
    
    // Initialize Course Animations
    initCourseAnimations();
    
    // Initialize New Release Course Animations
    initNewReleaseCourseAnimations();
    
    // Initialize Outstanding Categories
    initOutstandingCategories();
    
    // Button hover effects
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-start-learning, .widget-button');
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