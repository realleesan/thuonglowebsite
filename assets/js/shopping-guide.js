// Shopping Guide Page JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Step animations
    const steps = document.querySelectorAll('.step-item');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const stepObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, observerOptions);

    steps.forEach(step => {
        step.style.opacity = '0';
        step.style.transform = 'translateY(30px)';
        step.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        stepObserver.observe(step);
    });

    // Payment method hover effects
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        method.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        method.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(-5px) scale(1)';
        });
    });

    // Tip cards animation
    const tipCards = document.querySelectorAll('.tip-card');
    const tipObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 150);
            }
        });
    }, observerOptions);

    tipCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        tipObserver.observe(card);
    });


    // Smooth scroll to sections
    const sectionHeaders = document.querySelectorAll('.section-header');
    sectionHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function () {
            const rect = this.getBoundingClientRect();
            const offset = window.pageYOffset + rect.top - 100;

            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
        });
    });


    // Analytics tracking
    steps.forEach((step, index) => {
        step.addEventListener('click', function () {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'guide_step_click', {
                    'step_number': index + 1,
                    'step_title': this.querySelector('h3').textContent
                });
            }
        });
    });

    paymentMethods.forEach((method, index) => {
        method.addEventListener('click', function () {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'payment_method_click', {
                    'payment_method': this.querySelector('h3').textContent,
                    'method_position': index + 1
                });
            }
        });
    });
});
