// Contact Page JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Form elements
    const contactForm = document.querySelector('.contact-form-wrapper');
    const formControls = document.querySelectorAll('.form-control');
    const submitBtn = document.querySelector('.submit-btn');
    const checkboxInput = document.querySelector('input[name="your-consent"]');
    
    // Form validation
    const validators = {
        'your-name': {
            required: true,
            minLength: 2,
            pattern: /^[a-zA-Z\s]+$/,
            message: 'Please enter a valid name (letters and spaces only)'
        },
        'your-email': {
            required: true,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        'your-subject': {
            required: true,
            minLength: 5,
            message: 'Subject must be at least 5 characters long'
        },
        'your-message': {
            required: true,
            minLength: 10,
            message: 'Message must be at least 10 characters long'
        }
    };
    
    // Validation functions
    function validateField(field) {
        const name = field.name;
        const value = field.value.trim();
        const validator = validators[name];
        
        if (!validator) return true;
        
        // Remove existing error message
        removeErrorMessage(field);
        field.classList.remove('error', 'success');
        
        // Required validation
        if (validator.required && !value) {
            showError(field, 'This field is required');
            return false;
        }
        
        // Skip other validations if field is empty and not required
        if (!value && !validator.required) {
            return true;
        }
        
        // Min length validation
        if (validator.minLength && value.length < validator.minLength) {
            showError(field, `Minimum ${validator.minLength} characters required`);
            return false;
        }
        
        // Pattern validation
        if (validator.pattern && !validator.pattern.test(value)) {
            showError(field, validator.message);
            return false;
        }
        
        // Field is valid
        field.classList.add('success');
        return true;
    }
    
    function showError(field, message) {
        field.classList.add('error');
        
        let errorDiv = field.parentNode.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            field.parentNode.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
    }
    
    function removeErrorMessage(field) {
        const errorDiv = field.parentNode.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.classList.remove('show');
        }
    }
    
    function validateForm() {
        let isValid = true;
        
        formControls.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Real-time validation
    formControls.forEach(field => {
        // Validate on blur
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Remove error styling on input
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                this.classList.remove('error');
                removeErrorMessage(this);
            }
        });
        
        // Add focus effects
        field.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        field.addEventListener('blur', function() {
            this.parentNode.classList.remove('focused');
        });
    });
    
    // Checkbox functionality
    if (checkboxInput) {
        checkboxInput.addEventListener('change', function() {
            const label = this.closest('.checkbox-label');
            if (this.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        });
    }
    
    // Form submission
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                // Scroll to first error
                const firstError = document.querySelector('.form-control.error');
                if (firstError) {
                    firstError.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    firstError.focus();
                }
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Simulate form submission (replace with actual submission logic)
            setTimeout(() => {
                // Hide loading state
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                
                // Show success message
                showSuccessMessage();
                
                // Reset form
                contactForm.reset();
                
                // Remove validation classes
                formControls.forEach(field => {
                    field.classList.remove('error', 'success');
                    removeErrorMessage(field);
                });
                
                // Scroll to top of form
                contactForm.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
                
            }, 2000); // Simulate 2 second delay
        });
    }
    
    function showSuccessMessage() {
        // Remove existing success message
        const existingMessage = document.querySelector('.success-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Create success message
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message show';
        successDiv.innerHTML = `
            <strong>Thank you!</strong> Your message has been sent successfully. We'll get back to you soon.
        `;
        
        // Insert before form
        contactForm.parentNode.insertBefore(successDiv, contactForm);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            successDiv.classList.remove('show');
            setTimeout(() => {
                successDiv.remove();
            }, 300);
        }, 5000);
    }
    
    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
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
    
    // Card hover effects - REMOVED
    // const infoCards = document.querySelectorAll('.info-card');
    // infoCards.forEach(card => {
    //     card.addEventListener('mouseenter', function() {
    //         this.style.transform = 'translateY(-4px)';
    //     });
    //     
    //     card.addEventListener('mouseleave', function() {
    //         this.style.transform = 'translateY(0)';
    //     });
    // });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation - REMOVED info-card
    const animateElements = document.querySelectorAll('.contact-info, .contact-form');
    animateElements.forEach(el => {
        observer.observe(el);
    });
    
    // Character counter for textarea
    const messageField = document.querySelector('textarea[name="your-message"]');
    if (messageField) {
        const maxLength = 2000;
        
        // Create counter element
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.style.cssText = `
            font-size: 12px;
            color: #6B7280;
            text-align: right;
            margin-top: 4px;
        `;
        messageField.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = messageField.value.length;
            counter.textContent = `${length}/${maxLength}`;
            
            if (length > maxLength * 0.9) {
                counter.style.color = '#EF4444';
            } else if (length > maxLength * 0.7) {
                counter.style.color = '#F59E0B';
            } else {
                counter.style.color = '#6B7280';
            }
        }
        
        messageField.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    }
    
    // Auto-resize textarea
    function autoResize(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
    
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            autoResize(this);
        });
        
        // Initial resize
        autoResize(textarea);
    });
    
    // Keyboard navigation improvements
    document.addEventListener('keydown', function(e) {
        // Enter key on submit button
        if (e.key === 'Enter' && e.target === submitBtn) {
            e.preventDefault();
            submitBtn.click();
        }
        
        // Escape key to clear form
        if (e.key === 'Escape' && e.ctrlKey) {
            if (confirm('Are you sure you want to clear the form?')) {
                contactForm.reset();
                formControls.forEach(field => {
                    field.classList.remove('error', 'success');
                    removeErrorMessage(field);
                });
            }
        }
    });
    
    // Form auto-save to localStorage (optional)
    const STORAGE_KEY = 'contact_form_data';
    
    function saveFormData() {
        const formData = {};
        formControls.forEach(field => {
            if (field.type === 'checkbox') {
                formData[field.name] = field.checked;
            } else {
                formData[field.name] = field.value;
            }
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
    }
    
    function loadFormData() {
        try {
            const savedData = localStorage.getItem(STORAGE_KEY);
            if (savedData) {
                const formData = JSON.parse(savedData);
                formControls.forEach(field => {
                    if (formData.hasOwnProperty(field.name)) {
                        if (field.type === 'checkbox') {
                            field.checked = formData[field.name];
                        } else {
                            field.value = formData[field.name];
                        }
                    }
                });
            }
        } catch (e) {
            console.warn('Could not load saved form data:', e);
        }
    }
    
    function clearFormData() {
        localStorage.removeItem(STORAGE_KEY);
    }
    
    // Auto-save on input
    formControls.forEach(field => {
        field.addEventListener('input', debounce(saveFormData, 1000));
    });
    
    if (checkboxInput) {
        checkboxInput.addEventListener('change', saveFormData);
    }
    
    // Load saved data on page load
    loadFormData();
    
    // Clear saved data on successful submission
    contactForm.addEventListener('submit', function() {
        setTimeout(clearFormData, 2500); // Clear after success message
    });
    
    // Utility function for debouncing
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
    
    // Copy to clipboard functionality for contact info
    const contactInfoItems = document.querySelectorAll('.card-content');
    contactInfoItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Only for contact info card (second card)
            if (this.closest('.info-card:nth-child(2)')) {
                const text = this.textContent.trim();
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => {
                        showTooltip(this, 'Copied to clipboard!');
                    });
                }
            }
        });
    });
    
    function showTooltip(element, message) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = message;
        tooltip.style.cssText = `
            position: absolute;
            background: #1F2937;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        setTimeout(() => tooltip.style.opacity = '1', 10);
        
        setTimeout(() => {
            tooltip.style.opacity = '0';
            setTimeout(() => tooltip.remove(), 300);
        }, 2000);
    }
    
    // Accessibility improvements
    const focusableElements = document.querySelectorAll(
        'input, textarea, button, select, a[href], [tabindex]:not([tabindex="-1"])'
    );
    
    focusableElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.setAttribute('data-focused', 'true');
        });
        
        element.addEventListener('blur', function() {
            this.removeAttribute('data-focused');
        });
    });
    
    // Skip to main content functionality
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Skip to main content';
    skipLink.className = 'skip-link';
    skipLink.style.cssText = `
        position: absolute;
        top: -40px;
        left: 6px;
        background: #356DF1;
        color: white;
        padding: 8px;
        text-decoration: none;
        border-radius: 4px;
        z-index: 1000;
        transition: top 0.3s ease;
    `;
    
    skipLink.addEventListener('focus', function() {
        this.style.top = '6px';
    });
    
    skipLink.addEventListener('blur', function() {
        this.style.top = '-40px';
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        // Export any functions that might be useful for testing
    };
}