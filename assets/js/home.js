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
    initGenericSlider('.customer-says-section');
    initGenericSlider('.upcoming-events-section');
    initGenericSlider('.latest-news-section');

    // 5. Initialize Other Animations
    initCourseAnimations();
    initOutstandingCategories();
    initMissionSection();
    initCustomerSaysSection();
    initUpcomingEventsSection();
    initLatestNewsSection();
});

/**
 * Generic slider initialization logic to reuse for multiple sections
 */
function initGenericSlider(sectionSelector) {
    const section = document.querySelector(sectionSelector);
    if (!section) return;

    // Handle different slider structures
    let slider, slidesGrid, bullets, prevBtn, nextBtn;
    
    if (sectionSelector === '.customer-says-section') {
        // Customer Says has different structure
        const sliderWrapper = section.querySelector('.testimonial-slider-wrapper');
        slider = section.querySelector('.testimonial-slider');
        slidesGrid = slider?.querySelector('.testimonials-grid');
        bullets = section.querySelectorAll('.pagination-dot');
        prevBtn = sliderWrapper?.querySelector('.testimonial-nav-prev'); // Look in wrapper
        nextBtn = sliderWrapper?.querySelector('.testimonial-nav-next'); // Look in wrapper
    } else if (sectionSelector === '.upcoming-events-section') {
        // Upcoming Events structure
        slider = section.querySelector('.events-slider');
        slidesGrid = slider?.querySelector('.events-grid');
        bullets = section.querySelectorAll('.pagination-bullet');
        prevBtn = slider?.querySelector('.slider-nav-prev');
        nextBtn = slider?.querySelector('.slider-nav-next');
    } else if (sectionSelector === '.latest-news-section') {
        // Latest News structure
        slider = section.querySelector('.news-slider');
        slidesGrid = slider?.querySelector('.news-grid');
        bullets = section.querySelectorAll('.pagination-bullet');
        prevBtn = slider?.querySelector('.slider-nav-prev');
        nextBtn = slider?.querySelector('.slider-nav-next');
    } else {
        // Popular Courses and New Release structure
        slider = section.querySelector('.courses-slider');
        slidesGrid = slider?.querySelector('.courses-grid');
        bullets = section.querySelectorAll('.pagination-bullet');
        prevBtn = slider?.querySelector('.slider-nav-prev');
        nextBtn = slider?.querySelector('.slider-nav-next');
    }

    if (!slidesGrid) {
        console.log(`No slides grid found for ${sectionSelector}`);
        return;
    }

    // Debug logging for customer says
    if (sectionSelector === '.customer-says-section') {
        console.log('Customer Says Debug:', {
            section: !!section,
            slider: !!slider,
            slidesGrid: !!slidesGrid,
            bullets: bullets.length,
            prevBtn: !!prevBtn,
            nextBtn: !!nextBtn,
            totalItems: slidesGrid.children.length
        });
    }

    let currentIndex = 0;
    const totalItems = slidesGrid.children.length;
    
    // Different logic for different sections
    let itemsPerView, maxIndex, itemWidth, gap, moveDistance;
    
    if (sectionSelector === '.customer-says-section') {
        itemsPerView = 1; // Show 1 testimonial at a time
        maxIndex = Math.max(0, totalItems - itemsPerView);
        itemWidth = 1125; // Full width testimonial
        gap = 15;
        moveDistance = itemWidth + gap;
    } else if (sectionSelector === '.upcoming-events-section') {
        itemsPerView = 3; // Show 3 events at a time
        maxIndex = Math.max(0, totalItems - itemsPerView);
        itemWidth = 360; // Event item width
        gap = 15;
        moveDistance = itemWidth + gap;
    } else if (sectionSelector === '.latest-news-section') {
        itemsPerView = 3; // Show 3 news at a time
        maxIndex = Math.max(0, totalItems - itemsPerView);
        itemWidth = 360; // News item width
        gap = 15;
        moveDistance = itemWidth + gap;
    } else {
        itemsPerView = 4; // Show 4 courses at a time
        maxIndex = Math.max(0, totalItems - itemsPerView);
        itemWidth = 270;
        gap = 15;
        moveDistance = itemWidth + gap;
    }

    function updateSlider() {
        const translateX = -(currentIndex * moveDistance);
        slidesGrid.style.transform = `translateX(${translateX}px)`;

        // Debug logging for customer says
        if (sectionSelector === '.customer-says-section') {
            console.log(`Customer Says Update: currentIndex=${currentIndex}, translateX=${translateX}px`);
        }

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
            console.log(`${sectionSelector} bullet clicked: ${index}`);
            goToSlide(index);
        });
    });

    // Event listeners for arrows
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            console.log(`${sectionSelector} prev clicked, currentIndex: ${currentIndex}`);
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            console.log(`${sectionSelector} next clicked, currentIndex: ${currentIndex}`);
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSlider();
            }
        });
    }

    // Handle window resize
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
 * Mission Section Animation
 */
function initMissionSection() {
    const missionItems = document.querySelectorAll('.mission-item');
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

    const missionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    missionItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        missionObserver.observe(item);

        // Add hover effects for better interactivity
        item.addEventListener('mouseenter', function () {
            const icon = this.querySelector('.mission-icon');
            const title = this.querySelector('.mission-title');
            if (icon) {
                icon.style.transform = 'scale(1.1)';
            }
            if (title) {
                title.style.color = '#356DF1';
            }
        });

        item.addEventListener('mouseleave', function () {
            const icon = this.querySelector('.mission-icon');
            const title = this.querySelector('.mission-title');
            if (icon) {
                icon.style.transform = 'scale(1)';
            }
            if (title) {
                title.style.color = '#1F2937';
            }
        });
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
    });
}

function initCustomerSaysSection() {
    const customerSaysSection = document.querySelector('.customer-says-section');
    if (!customerSaysSection) return;

    const testimonialContainer = customerSaysSection.querySelector('.testimonials-container');
    if (!testimonialContainer) return;

    // Add entrance animation
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const testimonialObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    testimonialContainer.style.opacity = '0';
    testimonialContainer.style.transform = 'translateY(30px)';
    testimonialContainer.style.transition = 'all 0.8s ease';
    testimonialObserver.observe(testimonialContainer);
}

function initUpcomingEventsSection() {
    const upcomingEventsSection = document.querySelector('.upcoming-events-section');
    if (!upcomingEventsSection) return;

    const eventItems = upcomingEventsSection.querySelectorAll('.event-item');
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

    const eventObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    eventItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        eventObserver.observe(item);

        // Add hover effects for better interactivity
        item.addEventListener('mouseenter', function () {
            const image = this.querySelector('.event-image img');
            const title = this.querySelector('.event-title a');
            if (image) {
                image.style.transform = 'scale(1.05)';
            }
            if (title) {
                title.style.color = '#356DF1';
            }
        });

        item.addEventListener('mouseleave', function () {
            const image = this.querySelector('.event-image img');
            const title = this.querySelector('.event-title a');
            if (image) {
                image.style.transform = 'scale(1)';
            }
            if (title) {
                title.style.color = '#212427';
            }
        });
    });
}
function initLatestNewsSection() {
    const latestNewsSection = document.querySelector('.latest-news-section');
    if (!latestNewsSection) return;

    const newsItems = latestNewsSection.querySelectorAll('.news-item');
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

    const newsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    newsItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        newsObserver.observe(item);

        // Add hover effects for better interactivity
        item.addEventListener('mouseenter', function () {
            const image = this.querySelector('.news-image img');
            const title = this.querySelector('.news-title a');
            const readMore = this.querySelector('.read-more-btn');
            if (image) {
                image.style.transform = 'scale(1.05)';
            }
            if (title) {
                title.style.color = '#356DF1';
            }
            if (readMore) {
                readMore.style.color = '#356DF1';
            }
        });

        item.addEventListener('mouseleave', function () {
            const image = this.querySelector('.news-image img');
            const title = this.querySelector('.news-title a');
            const readMore = this.querySelector('.read-more-btn');
            if (image) {
                image.style.transform = 'scale(1)';
            }
            if (title) {
                title.style.color = '#212427';
            }
            if (readMore) {
                readMore.style.color = '#6B7280';
            }
        });
    });
}