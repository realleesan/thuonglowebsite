// Admin Footer JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const footerLinks = document.querySelectorAll('.footer-link');
    const versionElement = document.querySelector('.version');
    
    // Add smooth hover effects to footer links
    footerLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Version click easter egg
    if (versionElement) {
        let clickCount = 0;
        versionElement.addEventListener('click', function() {
            clickCount++;
            
            if (clickCount === 5) {
                showVersionInfo();
                clickCount = 0;
            }
        });
    }
    
    // Show version information modal
    function showVersionInfo() {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;
        
        const content = document.createElement('div');
        content.style.cssText = `
            background: white;
            padding: 24px;
            border-radius: 12px;
            max-width: 400px;
            text-align: center;
            font-family: 'Inter', sans-serif;
        `;
        
        content.innerHTML = `
            <h3 style="margin: 0 0 16px 0; color: #356DF1;">ThuongLo Admin v1.0.0</h3>
            <p style="margin: 0 0 16px 0; color: #6B7280;">
                Phát triển bởi MistyTeam<br>
                © 2024 ThuongLo. All rights reserved.
            </p>
            <button onclick="this.closest('.version-modal').remove()" 
                    style="background: #356DF1; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                Đóng
            </button>
        `;
        
        modal.className = 'version-modal';
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Close on click outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        // Auto close after 5 seconds
        setTimeout(() => {
            if (document.body.contains(modal)) {
                modal.remove();
            }
        }, 5000);
    }
    
    // Update copyright year automatically
    const copyrightElement = document.querySelector('.copyright');
    if (copyrightElement) {
        const currentYear = new Date().getFullYear();
        const copyrightText = copyrightElement.innerHTML;
        
        // Replace year if different
        if (!copyrightText.includes(currentYear.toString())) {
            copyrightElement.innerHTML = copyrightText.replace(/© \d{4}/, `© ${currentYear}`);
        }
    }
    
    // Add loading states to footer links
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't prevent default, just add visual feedback
            this.style.opacity = '0.7';
            
            setTimeout(() => {
                this.style.opacity = '1';
            }, 200);
        });
    });
    
    // Responsive footer adjustments
    function handleResponsiveFooter() {
        const footerContent = document.querySelector('.footer-content');
        
        if (window.innerWidth <= 768) {
            footerContent.style.flexDirection = 'column';
            footerContent.style.textAlign = 'center';
        } else {
            footerContent.style.flexDirection = 'row';
            footerContent.style.textAlign = 'left';
        }
    }
    
    window.addEventListener('resize', handleResponsiveFooter);
    handleResponsiveFooter(); // Initial check
});