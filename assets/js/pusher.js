// Scroll to Top Functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('back-to-top');
    const progressPath = backToTopBtn.querySelector('svg path');
    const pathLength = progressPath.getTotalLength();
    
    // Set initial path properties
    progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
    progressPath.style.strokeDashoffset = pathLength;
    
    // Update progress on scroll
    function updateProgress() {
        const scroll = window.pageYOffset;
        const height = document.documentElement.scrollHeight - window.innerHeight;
        const progress = pathLength - (scroll * pathLength / height);
        
        progressPath.style.strokeDashoffset = progress;
        
        // Show/hide button based on scroll position
        if (scroll > 300) {
            backToTopBtn.classList.add('active');
        } else {
            backToTopBtn.classList.remove('active');
        }
    }
    
    // Smooth scroll to top
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Event listeners
    window.addEventListener('scroll', updateProgress);
    backToTopBtn.addEventListener('click', scrollToTop);
    
    // Initial call
    updateProgress();
});