// Home Page JavaScript
document.addEventListener('DOMContentLoaded', function () {

    // 1. Hero Section Animation
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        const heroContent = heroSection.querySelector('.hero-content');
        if (heroContent) {
            heroContent.style.opacity = '0';
            heroContent.style.transform = 'translateY(30px)';

            setTimeout(() => {
                heroContent.style.transition = 'all(0.8s ease';
                heroContent.style.opacity = '1';
                heroContent.style.transform = 'translateY(0)';
            }, 100);
        }
    }

    // 2. Button hover effects
    const buttons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-start-learning, .widget-button');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-2px)';
        });
        button.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });

    // 3. Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // 4. Initialize Sliders
    initGenericSlider('.popular-courses-section');
    initGenericSlider('.new-release-section');

    // 5. Initialize Other Animations
    initCourseAnimations();
    initOutstandingCategories();
});

/**
 * Generic slider initialization logic to reuse for multiple sections
 */
function initGenericSlider(sectionSelector) {
    const section = document.querySelector(sectionSelector);
    if (!section) return;

    const slider = section.querySelector('.courses-slider');
    if (!slider) return;

    const coursesGrid = slider.querySelector('.courses-grid');
    const bullets = section.querySelectorAll('.pagination-bullet');
    const prevBtn = slider.querySelector('.slider-nav-prev');
    const nextBtn = slider.querySelector('.slider-nav-next');

    if (!coursesGrid) return;

    let currentIndex = 0;
    const totalItems = coursesGrid.children.length;
    const itemsPerView = 4;
    const maxIndex = Math.max(0, totalItems - itemsPerView);

    const itemWidth = 270;
    const gap = 15;
    const moveDistance = itemWidth + gap;

    function updateSlider() {
        const translateX = -(currentIndex * moveDistance);
        coursesGrid.style.transform = `translateX(${translateX}px)`;

        // Update pagination bullets
        bullets.forEach((bullet, index) => {
            bullet.classList.toggle('active', index === currentIndex);
        });

        // Update navigation buttons
        if (prevBtn) {
            prevBtn.style.opacity = currentIndex === 0 ? '0.3' : '1';
            prevBtn.style.pointerEvents = currentIndex === 0 ? 'none' : 'auto';
        }
        if (nextBtn) {
            nextBtn.style.opacity = currentIndex === maxIndex ? '0.3' : '1';
            nextBtn.style.pointerEvents = currentIndex === maxIndex ? 'none' : 'auto';
        }
    }

    function goToSlide(slideIndex) {
        if (slideIndex >= 0 && slideIndex <= maxIndex) {
            currentIndex = slideIndex;
            updateSlider();
        }
    }

    // Event listeners for bullets
    bullets.forEach((bullet, index) => {
        bullet.addEventListener('click', () => {
            goToSlide(index);
        });
    });

    // Event listeners for arrows
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSlider();
            }
        });
    }

    // Handle window resize (re-center if needed, though using fixed items)
    window.addEventListener('resize', updateSlider);

    // Initialize
    updateSlider();
}

/**
 * Image lazy loading
 */
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

if ('IntersectionObserver' in window) {
    lazyLoadImages();
}

/**
 * Course Item Animations
 */
function initCourseAnimations() {
    const courseItems = document.querySelectorAll('.course-item');
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

    const courseObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    courseItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        courseObserver.observe(item);
    });
}

/**
 * Outstanding Categories Animation
 */
function initOutstandingCategories() {
    const categoryItems = document.querySelectorAll('.thim-widget-course-categories-grid li');
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

    const categoryObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    categoryItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        categoryObserver.observe(item);

        const link = item.querySelector('a');
        if (link) {
            link.addEventListener('mouseenter', function () {
                item.style.transform = 'translateY(-5px) scale(1.02)';
            });
            link.addEventListener('mouseleave', function () {
                item.style.transform = 'translateY(0) scale(1)';
            });
        }
    });
}