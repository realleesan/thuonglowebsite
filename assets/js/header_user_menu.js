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

// Agent Registration Functions
function showAgentRegistrationPopup() {
    // Create and show agent registration popup
    const popup = document.createElement('div');
    popup.className = 'agent-popup-overlay';
    popup.innerHTML = `
        <div class="agent-popup">
            <div class="agent-popup-header">
                <h3>Đăng ký trở thành đại lý</h3>
                <button type="button" class="close-popup" onclick="closeAgentPopup()">&times;</button>
            </div>
            <div class="agent-popup-content">
                <form id="agentRegistrationForm" action="/api/agent/register" method="POST">
                    <div class="form-group">
                        <label for="agent_email">Email Gmail (bắt buộc) *</label>
                        <input type="email" id="agent_email" name="agent_email" required 
                               pattern=".*@gmail\\.com$" 
                               title="Vui lòng nhập địa chỉ Gmail hợp lệ"
                               placeholder="example@gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="agent_info">Thông tin bổ sung</label>
                        <textarea id="agent_info" name="agent_info" rows="3" 
                                  placeholder="Kinh nghiệm, lý do muốn trở thành đại lý..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" onclick="closeAgentPopup()" class="btn-cancel">Hủy</button>
                        <button type="submit" class="btn-submit">Gửi yêu cầu</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    // Handle form submission
    document.getElementById('agentRegistrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAgentRegistration(this);
    });
    
    // Close popup when clicking overlay
    popup.addEventListener('click', function(e) {
        if (e.target === popup) {
            closeAgentPopup();
        }
    });
}

function closeAgentPopup() {
    const popup = document.querySelector('.agent-popup-overlay');
    if (popup) {
        popup.remove();
    }
}

function submitAgentRegistration(form) {
    const formData = new FormData(form);
    
    // Show loading state
    const submitBtn = form.querySelector('.btn-submit');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Đang xử lý...';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAgentMessage('Yêu cầu đăng ký đại lý đã được gửi thành công! Chúng tôi sẽ xử lý trong vòng 24 giờ.', 'success');
            closeAgentPopup();
            
            // Update page to reflect pending status
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showAgentMessage(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAgentMessage('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function showAgentProcessingMessage() {
    showAgentMessage('Yêu cầu đăng ký đại lý của bạn đang được xử lý. Chúng tôi sẽ phản hồi trong vòng 24 giờ.', 'info');
}

function showAgentMessage(message, type = 'info') {
    // Remove existing message
    const existingMessage = document.querySelector('.agent-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `agent-message agent-message-${type}`;
    messageDiv.innerHTML = `
        <div class="agent-message-content">
            <span>${message}</span>
            <button type="button" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentElement) {
            messageDiv.remove();
        }
    }, 5000);
}