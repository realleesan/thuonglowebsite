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
    document.querySelectorAll('.popular-courses-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.new-release-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.customer-says-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.upcoming-events-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.latest-news-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.featured-brands-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.latest-products-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.budget-products-section').forEach(el => initGenericSlider(el));
    document.querySelectorAll('.sale-products-section').forEach(el => initGenericSlider(el));

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
function initGenericSlider(sectionOrSelector) {
    const section = typeof sectionOrSelector === 'string' ? document.querySelector(sectionOrSelector) : sectionOrSelector;
    if (!section) return;

    const sectionSelector = typeof sectionOrSelector === 'string' ? sectionOrSelector : (section.id ? '#' + section.id : '.' + section.className.trim().replace(/\s+/g, '.'));

    // Handle different slider structures
    let slider, slidesGrid, bullets, prevBtn, nextBtn;

    const isCustomerSays = section.classList.contains('customer-says-section');
    const isUpcomingEvents = section.classList.contains('upcoming-events-section');
    const isLatestNews = section.classList.contains('latest-news-section');
    const isFeaturedBrands = section.classList.contains('featured-brands-section');

    if (isCustomerSays) {
        const sliderWrapper = section.querySelector('.testimonial-slider-wrapper');
        slider = section.querySelector('.testimonial-slider');
        slidesGrid = slider?.querySelector('.testimonials-grid');
        bullets = section.querySelectorAll('.pagination-dot');
        prevBtn = sliderWrapper?.querySelector('.testimonial-nav-prev');
        nextBtn = sliderWrapper?.querySelector('.testimonial-nav-next');
    } else if (isUpcomingEvents) {
        slider = section.querySelector('.events-slider');
        slidesGrid = slider?.querySelector('.events-grid');
        bullets = section.querySelectorAll('.pagination-bullet');
        prevBtn = slider?.querySelector('.slider-nav-prev');
        nextBtn = slider?.querySelector('.slider-nav-next');
    } else if (isLatestNews) {
        slider = section.querySelector('.news-slider');
        slidesGrid = slider?.querySelector('.news-grid');
        bullets = section.querySelectorAll('.pagination-bullet');
        prevBtn = slider?.querySelector('.slider-nav-prev');
        nextBtn = slider?.querySelector('.slider-nav-next');
    } else {
        slider = section.querySelector('.courses-slider');
        slidesGrid = slider?.querySelector('.courses-grid');
        bullets = section.querySelectorAll('.pagination-bullet');
        prevBtn = section?.querySelector('.slider-nav-prev');
        nextBtn = section?.querySelector('.slider-nav-next');
    }

    if (!slidesGrid || !slidesGrid.children.length) {
        return;
    }

    let currentIndex = 0;
    const totalItems = slidesGrid.children.length;

    // Responsive variables
    let itemsPerView, maxIndex, itemWidth, gap, moveDistance;

    function calculateDimensions() {
        const viewportWidth = window.innerWidth;

        // 1. Determine items per view based on screen width
        if (isCustomerSays) {
            itemsPerView = 1;
        } else if (isUpcomingEvents || isLatestNews) {
            if (viewportWidth >= 1024) itemsPerView = 3;
            else if (viewportWidth >= 768) itemsPerView = 2;
            else itemsPerView = 1;
        } else if (isFeaturedBrands) {
            if (viewportWidth >= 1024) itemsPerView = 4;
            else if (viewportWidth >= 768) itemsPerView = 3;
            else if (viewportWidth >= 480) itemsPerView = 2;
            else itemsPerView = 1;
        } else {
            // Products & courses sliders
            if (viewportWidth >= 1024) itemsPerView = 4;
            else if (viewportWidth >= 768) itemsPerView = 3;
            else if (viewportWidth >= 480) itemsPerView = 2;
            else itemsPerView = 1;
        }

        maxIndex = Math.max(0, totalItems - itemsPerView);
        if (currentIndex > maxIndex) {
            currentIndex = maxIndex;
        }

        // 2. Measure actual rendered width and gap dynamically from DOM
        const firstItem = slidesGrid.children[0];
        if (firstItem) {
            // Get actual computed column gap from container
            const computedStyle = window.getComputedStyle(slidesGrid);
            const gridGap = parseFloat(computedStyle.gap || computedStyle.columnGap) || 15;
            gap = gridGap;

            // Measure actual client width of a slide item
            itemWidth = firstItem.getBoundingClientRect().width;
            moveDistance = itemWidth + gap;
        } else {
            // Fallback default calculations
            itemWidth = 270;
            gap = 15;
            moveDistance = itemWidth + gap;
        }
    }

    function updateSlider() {
        // Recalculate dimensions in case viewport size changed
        calculateDimensions();

        const translateX = -(currentIndex * moveDistance);
        slidesGrid.style.transform = `translateX(${translateX}px)`;

        // Update pagination bullets
        bullets.forEach((bullet, index) => {
            bullet.classList.toggle('active', index === currentIndex);
        });

        // Update navigation buttons styling and accessibility
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

    // Event listeners for pagination bullets
    bullets.forEach((bullet, index) => {
        bullet.addEventListener('click', () => {
            goToSlide(index);
        });
    });

    // Event listeners for navigation arrows
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

    // Mobile Swipe Gestures
    let touchStartX = 0;
    let touchEndX = 0;

    slidesGrid.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    slidesGrid.addEventListener('touchend', function (e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const swipeThreshold = 40; // minimum drag distance in px
        if (touchStartX - touchEndX > swipeThreshold) {
            // Swiped left -> Go to Next Slide
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateSlider();
            }
        } else if (touchEndX - touchStartX > swipeThreshold) {
            // Swiped right -> Go to Previous Slide
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            }
        }
    }

    // Handle window resize dynamically
    window.addEventListener('resize', debounce(() => {
        updateSlider();
    }, 150));

    // Initialize dimensions and positioning
    calculateDimensions();
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
    if (window.innerWidth <= 768) {
        // Skip hiding on mobile to prevent slider items remaining blank/invisible
        courseItems.forEach(item => {
            item.style.opacity = '1';
            item.style.transform = 'none';
        });
        return;
    }

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

    // Add hover effects for better interactivity regardless of screen size
    missionItems.forEach((item) => {
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

    if (window.innerWidth <= 768) {
        missionItems.forEach(item => {
            item.style.opacity = '1';
            item.style.transform = 'none';
        });
        return;
    }

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
    });
}

/**
 * Outstanding Categories Animation
 */
function initOutstandingCategories() {
    const categoryItems = document.querySelectorAll('.thim-widget-course-categories-grid li');
    if (window.innerWidth <= 768) {
        categoryItems.forEach(item => {
            item.style.opacity = '1';
            item.style.transform = 'none';
        });
        return;
    }

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

    if (window.innerWidth <= 768) {
        testimonialContainer.style.opacity = '1';
        testimonialContainer.style.transform = 'none';
        return;
    }

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

    eventItems.forEach((item) => {
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

    if (window.innerWidth <= 768) {
        eventItems.forEach(item => {
            item.style.opacity = '1';
            item.style.transform = 'none';
        });
        return;
    }

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
    });
}

function initLatestNewsSection() {
    const latestNewsSection = document.querySelector('.latest-news-section');
    if (!latestNewsSection) return;

    const newsItems = latestNewsSection.querySelectorAll('.news-item');

    newsItems.forEach((item) => {
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

    if (window.innerWidth <= 768) {
        newsItems.forEach(item => {
            item.style.opacity = '1';
            item.style.transform = 'none';
        });
        return;
    }

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
    });
}

/**
 * Debounce helper function
 */
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}