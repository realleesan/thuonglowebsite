/**
 * User Data List Page JavaScript
 * Handles countdown timer for token expiration
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initCountdownTimer();
});

/**
 * Initialize countdown timer
 */
function initCountdownTimer() {
    const countdownEl = document.getElementById('countdown');
    if (!countdownEl) return;
    
    // Get expiration time from data attribute or calculate from initial value
    let expiresAt = countdownEl.getAttribute('data-expires-at');
    
    if (expiresAt) {
        // Use server timestamp
        updateCountdownFromTimestamp(countdownEl, expiresAt);
    } else {
        // Parse from text content (fallback)
        const text = countdownEl.textContent;
        const match = text.match(/(\d+)\s*phút\s*(\d+)\s*giây/);
        if (match) {
            let timeRemaining = parseInt(match[1]) * 60 + parseInt(match[2]);
            updateCountdownFromSeconds(countdownEl, timeRemaining);
        }
    }
}

/**
 * Update countdown from timestamp
 */
function updateCountdownFromTimestamp(countdownEl, expiresAt) {
    function update() {
        const now = Math.floor(Date.now() / 1000);
        const expires = parseInt(expiresAt);
        let timeRemaining = expires - now;
        
        if (timeRemaining <= 0) {
            countdownEl.innerHTML = '<i class="fas fa-clock"></i> Phiên đã hết hạn!';
            countdownEl.classList.add('expired');
            countdownEl.classList.remove('warning');
            // Auto reload page after 2 seconds to show expired message
            setTimeout(function() {
                window.location.reload();
            }, 2000);
            return;
        }
        
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        countdownEl.innerHTML = '<i class="fas fa-clock"></i> ' + minutes + ' phút ' + seconds + ' giây';
        
        // Color changes based on time remaining
        if (timeRemaining <= 60) {
            countdownEl.classList.add('expired');
            countdownEl.classList.remove('warning');
        } else if (timeRemaining <= 300) {
            countdownEl.classList.add('warning');
            countdownEl.classList.remove('expired');
        } else {
            countdownEl.classList.remove('expired', 'warning');
        }
    }
    
    update();
    setInterval(update, 1000);
}

/**
 * Update countdown from seconds
 */
function updateCountdownFromSeconds(countdownEl, initialSeconds) {
    let timeRemaining = initialSeconds;
    
    function update() {
        timeRemaining--;
        
        if (timeRemaining <= 0) {
            countdownEl.innerHTML = '<i class="fas fa-clock"></i> Phiên đã hết hạn!';
            countdownEl.classList.add('expired');
            countdownEl.classList.remove('warning');
            return;
        }
        
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        countdownEl.innerHTML = '<i class="fas fa-clock"></i> ' + minutes + ' phút ' + seconds + ' giây';
        
        // Color changes based on time remaining
        if (timeRemaining <= 60) {
            countdownEl.classList.add('expired');
            countdownEl.classList.remove('warning');
        } else if (timeRemaining <= 300) {
            countdownEl.classList.add('warning');
            countdownEl.classList.remove('expired');
        } else {
            countdownEl.classList.remove('expired', 'warning');
        }
    }
    
    update();
    setInterval(update, 1000);
}
