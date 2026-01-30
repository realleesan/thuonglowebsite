// Course Details Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initTabs();
    initCurriculumSections();
    initEnrollButton();
    initSmoothScrolling();
});

// Tab functionality
function initTabs() {
    const tabItems = document.querySelectorAll('.tab-item');
    const tabPanels = document.querySelectorAll('.tab-panel');
    
    tabItems.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and panels
            tabItems.forEach(item => item.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding panel
            this.classList.add('active');
            const targetPanel = document.getElementById(targetTab);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
            
            // Smooth scroll to tabs section on mobile
            if (window.innerWidth <= 768) {
                document.querySelector('.course-tabs').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Curriculum sections toggle functionality
function initCurriculumSections() {
    const sectionHeaders = document.querySelectorAll('.section-header');
    
    sectionHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        
        header.addEventListener('click', function() {
            const section = this.parentElement;
            const lessons = section.querySelector('.section-lessons');
            
            if (lessons) {
                const isExpanded = lessons.style.display !== 'none';
                
                // Toggle lessons visibility
                lessons.style.display = isExpanded ? 'none' : 'block';
                
                // Add/remove expanded class for styling
                section.classList.toggle('expanded', !isExpanded);
                
                // Add arrow indicator
                if (!header.querySelector('.toggle-arrow')) {
                    const arrow = document.createElement('span');
                    arrow.className = 'toggle-arrow';
                    arrow.innerHTML = '▼';
                    arrow.style.marginLeft = '8px';
                    arrow.style.transition = 'transform 0.3s ease';
                    header.appendChild(arrow);
                }
                
                const arrow = header.querySelector('.toggle-arrow');
                if (arrow) {
                    arrow.style.transform = isExpanded ? 'rotate(-90deg)' : 'rotate(0deg)';
                }
            }
        });
    });
}

// Enroll button functionality
function initEnrollButton() {
    const enrollBtn = document.querySelector('.btn-enroll');
    
    if (enrollBtn) {
        enrollBtn.addEventListener('click', function() {
            // Add loading state
            const originalText = this.textContent;
            this.textContent = 'Processing...';
            this.disabled = true;
            
            // Simulate enrollment process
            setTimeout(() => {
                // Check if user is logged in (this would be a real check in production)
                const isLoggedIn = false; // This would come from your auth system
                
                if (isLoggedIn) {
                    // Redirect to course content or show success message
                    showNotification('Successfully enrolled! Redirecting to course...', 'success');
                    setTimeout(() => {
                        // window.location.href = '/course-content';
                    }, 2000);
                } else {
                    // Redirect to login page
                    showNotification('Please log in to enroll in this course.', 'info');
                    setTimeout(() => {
                        window.location.href = '/?page=auth';
                    }, 2000);
                }
                
                // Reset button
                this.textContent = originalText;
                this.disabled = false;
            }, 1500);
        });
    }
}

// Smooth scrolling for internal links
function initSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '9999',
        maxWidth: '300px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease'
    });
    
    // Set background color based on type
    const colors = {
        success: '#16a34a',
        error: '#dc2626',
        warning: '#d97706',
        info: '#2563eb'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Handle responsive behavior
function handleResponsive() {
    const sidebar = document.querySelector('.course-sidebar');
    const main = document.querySelector('.course-details-main');
    
    function checkScreenSize() {
        if (window.innerWidth <= 1024) {
            // Mobile/tablet behavior
            if (sidebar && main) {
                sidebar.style.position = 'static';
            }
        } else {
            // Desktop behavior
            if (sidebar) {
                sidebar.style.position = 'sticky';
            }
        }
    }
    
    // Check on load and resize
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
}

// Initialize responsive behavior
handleResponsive();

// Add scroll-based animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.objective-item, .curriculum-section, .include-item');
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(el);
    });
}

// Initialize scroll animations when page loads
window.addEventListener('load', initScrollAnimations);

// Handle lesson item interactions
function initLessonInteractions() {
    const lessonItems = document.querySelectorAll('.lesson-item');
    
    lessonItems.forEach(lesson => {
        lesson.addEventListener('click', function() {
            // Add click effect
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // Here you would typically navigate to the lesson or show lesson details
            const lessonTitle = this.querySelector('.lesson-title').textContent;
            showNotification(`Opening lesson: ${lessonTitle}`, 'info');
        });
        
        // Add transition for smooth hover effects
        lesson.style.transition = 'all 0.2s ease';
    });
}

// Initialize lesson interactions
initLessonInteractions();

// Add keyboard navigation support
document.addEventListener('keydown', function(e) {
    // Tab navigation for tabs
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        const activeTab = document.querySelector('.tab-item.active');
        if (activeTab && document.activeElement === activeTab) {
            e.preventDefault();
            const tabs = Array.from(document.querySelectorAll('.tab-item'));
            const currentIndex = tabs.indexOf(activeTab);
            let nextIndex;
            
            if (e.key === 'ArrowLeft') {
                nextIndex = currentIndex > 0 ? currentIndex - 1 : tabs.length - 1;
            } else {
                nextIndex = currentIndex < tabs.length - 1 ? currentIndex + 1 : 0;
            }
            
            tabs[nextIndex].click();
            tabs[nextIndex].focus();
        }
    }
    
    // Escape key to close any open modals or notifications
    if (e.key === 'Escape') {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
    }
});

// Add focus styles for better accessibility
const style = document.createElement('style');
style.textContent = `
    .tab-item:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }
    
    .lesson-item:focus {
        outline: 2px solid #2563eb;
        outline-offset: -2px;
    }
    
    .btn-enroll:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }
`;
document.head.appendChild(style);