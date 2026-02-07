/**
 * Affiliate Dashboard JavaScript
 * Handles dashboard-specific interactions
 */

(function() {
    'use strict';
    
    /**
     * Initialize dashboard
     */
    function initDashboard() {
        console.log('Affiliate Dashboard initialized');
        
        // Dashboard-specific initialization
        // Note: copyToClipboard() is available from affiliate_main.js
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }
    
})();
