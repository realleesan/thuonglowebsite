// Flash Messages JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(function(message) {
        // Auto-hide after 5 seconds
        setTimeout(function() {
            if (message && message.parentElement) {
                message.style.animation = 'fadeOut 0.5s ease-out forwards';
                setTimeout(function() {
                    if (message && message.parentElement) {
                        message.remove();
                    }
                }, 500);
            }
        }, 5000);
        
        // Close button functionality
        const closeBtn = message.querySelector('.flash-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                message.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(function() {
                    if (message && message.parentElement) {
                        message.remove();
                    }
                }, 300);
            });
        }
    });
});