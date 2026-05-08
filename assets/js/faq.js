// FAQ Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // FAQ Accordion
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        
        question.addEventListener('click', function() {
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.faq-answer').style.display = 'none';
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
            
            if (item.classList.contains('active')) {
                answer.style.display = 'block';
            } else {
                answer.style.display = 'none';
            }
        });
    });
    
    // Search functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Tìm kiếm câu hỏi...';
    searchInput.className = 'faq-search';
    
    const sectionHeader = document.querySelector('.section-header');
    if (sectionHeader) {
        sectionHeader.appendChild(searchInput);
        
        // Add search styles
        searchInput.style.cssText = `
            width: 100%;
            max-width: 400px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 16px;
            margin-top: 20px;
            outline: none;
            transition: border-color 0.3s ease;
        `;
        
        searchInput.addEventListener('focus', function() {
            this.style.borderColor = '#007bff';
        });
        
        searchInput.addEventListener('blur', function() {
            this.style.borderColor = '#e9ecef';
        });
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide categories based on visible items
            const categories = document.querySelectorAll('.faq-category');
            categories.forEach(category => {
                const visibleItems = category.querySelectorAll('.faq-item[style="display: block"]');
                if (visibleItems.length === 0 && searchTerm !== '') {
                    category.style.display = 'none';
                } else {
                    category.style.display = 'block';
                }
            });
        });
    }
    
    // Smooth scroll to FAQ categories
    const categoryLinks = document.querySelectorAll('.faq-category h3');
    categoryLinks.forEach((link, index) => {
        link.style.cursor = 'pointer';
        link.addEventListener('click', function() {
            const rect = this.getBoundingClientRect();
            const offset = window.pageYOffset + rect.top - 100;
            
            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
        });
    });
    
    // Print functionality
    const printButton = document.createElement('button');
    printButton.textContent = 'In trang';
    printButton.className = 'print-button';
    printButton.style.cssText = `
        position: fixed;
        bottom: 30px;
        left: 30px;
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        z-index: 1000;
    `;
    
    document.body.appendChild(printButton);
    
    printButton.addEventListener('click', function() {
        window.print();
    });
    
    printButton.addEventListener('mouseenter', function() {
        this.style.background = '#5a6268';
        this.style.transform = 'translateY(-2px)';
    });
    
    printButton.addEventListener('mouseleave', function() {
        this.style.background = '#6c757d';
        this.style.transform = 'translateY(0)';
    });
    
    // Analytics tracking
    faqItems.forEach((item, index) => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', function() {
            // Track FAQ interaction
            if (typeof gtag !== 'undefined') {
                gtag('event', 'faq_click', {
                    'faq_category': item.closest('.faq-category').querySelector('h3').textContent,
                    'faq_question': this.textContent,
                    'faq_position': index + 1
                });
            }
        });
    });
});
