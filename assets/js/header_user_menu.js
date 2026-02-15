// User Menu Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    const userMenu = document.querySelector('.user-menu');
    const userBtn = document.querySelector('.user-btn');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (userMenu && userBtn && userDropdown) {
        // Toggle dropdown on button click
        userBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns first
            closeAllDropdowns();
            
            // Toggle current dropdown
            userDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Handle all dropdown menus
    const dropdownBtns = document.querySelectorAll('.dropdown-btn');
    const dropdownMenus = document.querySelectorAll('.has-dropdown .dropdown-menu');
    
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parent = this.closest('.has-dropdown');
            const menu = parent.querySelector('.dropdown-menu');
            
            if (menu) {
                // Close other dropdowns first
                closeAllDropdowns();
                
                // Toggle current dropdown
                menu.classList.toggle('show');
                parent.classList.toggle('active');
            }
        });
    });
    
    // Close all dropdowns
    function closeAllDropdowns() {
        dropdownMenus.forEach(menu => {
            menu.classList.remove('show');
            const parent = menu.closest('.has-dropdown');
            if (parent) {
                parent.classList.remove('active');
            }
        });
        
        if (userDropdown) {
            userDropdown.classList.remove('show');
        }
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const isDropdownClick = e.target.closest('.has-dropdown') || e.target.closest('.user-menu');
        
        if (!isDropdownClick) {
            closeAllDropdowns();
        }
    });
    
    // Handle hover for desktop
    const hasDropdowns = document.querySelectorAll('.has-dropdown');
    
    hasDropdowns.forEach(dropdown => {
        let hoverTimeout;
        
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            
            // Close other dropdowns first
            closeAllDropdowns();
            
            // Show current dropdown
            const menu = this.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.add('show');
                this.classList.add('active');
            }
        });
        
        dropdown.addEventListener('mouseleave', function() {
            const menu = this.querySelector('.dropdown-menu');
            const parent = this;
            
            hoverTimeout = setTimeout(() => {
                if (menu) {
                    menu.classList.remove('show');
                    parent.classList.remove('active');
                }
            }, 300); // Small delay to prevent flickering
        });
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
});