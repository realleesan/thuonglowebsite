// Shopping Guide Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
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
        method.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        method.addEventListener('mouseleave', function() {
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
    
    // Progress indicator
    const progressIndicator = document.createElement('div');
    progressIndicator.className = 'progress-indicator';
    progressIndicator.innerHTML = `
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
        <div class="progress-text">0%</div>
    `;
    
    progressIndicator.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: rgba(0,0,0,0.1);
        z-index: 1000;
    `;
    
    const progressBar = progressIndicator.querySelector('.progress-fill');
    const progressText = progressIndicator.querySelector('.progress-text');
    
    progressBar.style.cssText = `
        height: 100%;
        background: linear-gradient(90deg, #007bff, #28a745);
        width: 0%;
        transition: width 0.3s ease;
    `;
    
    progressText.style.cssText = `
        position: fixed;
        top: 10px;
        right: 20px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
    `;
    
    document.body.appendChild(progressIndicator);
    
    // Update progress on scroll
    function updateProgress() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        
        progressBar.style.width = scrollPercent + '%';
        progressText.textContent = Math.round(scrollPercent) + '%';
    }
    
    window.addEventListener('scroll', updateProgress);
    
    // Smooth scroll to sections
    const sectionHeaders = document.querySelectorAll('.section-header');
    sectionHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const rect = this.getBoundingClientRect();
            const offset = window.pageYOffset + rect.top - 100;
            
            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
        });
    });
    
    // Copy step functionality
    const copyButtons = document.createElement('div');
    copyButtons.className = 'copy-buttons';
    copyButtons.innerHTML = `
        <button class="copy-steps" title="Sao chép các bước mua hàng">
            <i class="fas fa-copy"></i>
        </button>
    `;
    
    copyButtons.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
    `;
    
    const copyBtn = copyButtons.querySelector('.copy-steps');
    copyBtn.style.cssText = `
        background: linear-gradient(135deg, #007bff, #28a745);
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(copyButtons);
    
    copyBtn.addEventListener('click', function() {
        const stepsText = Array.from(steps).map((step, index) => {
            const title = step.querySelector('h3').textContent;
            const tips = Array.from(step.querySelectorAll('.step-tips li')).map(li => li.textContent).join('\n');
            return `${index + 1}. ${title}\n${tips}`;
        }).join('\n\n');
        
        navigator.clipboard.writeText(stepsText).then(() => {
            this.style.background = '#28a745';
            this.innerHTML = '<i class="fas fa-check"></i>';
            
            setTimeout(() => {
                this.style.background = 'linear-gradient(135deg, #007bff, #28a745)';
                this.innerHTML = '<i class="fas fa-copy"></i>';
            }, 2000);
        });
    });
    
    copyBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px) scale(1.1)';
    });
    
    copyBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
    
    // Analytics tracking
    steps.forEach((step, index) => {
        step.addEventListener('click', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'guide_step_click', {
                    'step_number': index + 1,
                    'step_title': this.querySelector('h3').textContent
                });
            }
        });
    });
    
    paymentMethods.forEach((method, index) => {
        method.addEventListener('click', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'payment_method_click', {
                    'payment_method': this.querySelector('h3').textContent,
                    'method_position': index + 1
                });
            }
        });
    });
});
