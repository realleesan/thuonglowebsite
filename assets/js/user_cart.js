// User Cart JavaScript - Interactive Shopping Cart
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart functionality
    initCartFunctionality();
    
    // Initialize checkbox functionality
    initCartCheckboxes();
    
    console.log('User Cart JavaScript loaded successfully');
});

function initCartFunctionality() {
    // Quantity controls
    initQuantityControls();
    
    // Remove item functionality
    initRemoveItems();
    
    // Clear cart functionality
    initClearCart();
    
    // Checkout selected items
    initCheckoutSelected();
    
    // Note: updateCartTotals is called when needed after quantity changes
}

// Initialize checkbox functionality
function initCartCheckboxes() {
    const selectAllCheckbox = document.getElementById('selectAllCart');
    const itemCheckboxes = document.querySelectorAll('.cart-item-checkbox');
    
    // Select all checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const cartItem = checkbox.closest('.cart-item');
                if (cartItem) {
                    if (isChecked) {
                        cartItem.classList.add('selected');
                    } else {
                        cartItem.classList.remove('selected');
                    }
                }
            });
            
            updateSelectedCount();
            updateCheckoutButton();
        });
    }
    
    // Individual item checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const cartItem = this.closest('.cart-item');
            if (cartItem) {
                if (this.checked) {
                    cartItem.classList.add('selected');
                } else {
                    cartItem.classList.remove('selected');
                }
            }
            
            // Update select all checkbox state
            updateSelectAllCheckbox();
            updateSelectedCount();
            updateCheckoutButton();
        });
        
        // Initial state
        const cartItem = checkbox.closest('.cart-item');
        if (cartItem && checkbox.checked) {
            cartItem.classList.add('selected');
        }
    });
    
    updateSelectedCount();
    updateCheckoutButton();
}

// Update select all checkbox state
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAllCart');
    const itemCheckboxes = document.querySelectorAll('.cart-item-checkbox');
    
    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        const allChecked = Array.from(itemCheckboxes).every(checkbox => checkbox.checked);
        selectAllCheckbox.checked = allChecked;
    }
}

// Update selected count display
function updateSelectedCount() {
    const checkedCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
    const countElement = document.getElementById('selectedCount');
    
    if (countElement) {
        countElement.textContent = checkedCheckboxes.length;
    }
    
    // Update select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCart');
    if (selectAllCheckbox) {
        const totalCheckboxes = document.querySelectorAll('.cart-item-checkbox').length;
        selectAllCheckbox.checked = checkedCheckboxes.length === totalCheckboxes && totalCheckboxes > 0;
        selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < totalCheckboxes;
    }
}

// Update checkout button with selected count and total
function updateCheckoutButton() {
    const checkedCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
    const checkoutButton = document.getElementById('checkoutSelected');
    const checkoutCount = document.getElementById('checkoutCount');
    
    if (checkoutCount) {
        checkoutCount.textContent = checkedCheckboxes.length;
    }
    
    // Calculate selected total
    let selectedTotal = 0;
    checkedCheckboxes.forEach(checkbox => {
        selectedTotal += parseFloat(checkbox.dataset.price) || 0;
    });
    
    // Update total display - show selected items total or 0 when none selected
    const totalAmountElement = document.getElementById('cartTotalAmount');
    const cartItemCountElement = document.getElementById('cartItemCount');
    
    if (totalAmountElement) {
        if (checkedCheckboxes.length === 0) {
            totalAmountElement.textContent = formatCurrency(0);
        } else {
            totalAmountElement.textContent = formatCurrency(selectedTotal);
        }
    }
    
    // Update item count display
    if (cartItemCountElement) {
        cartItemCountElement.textContent = `(${checkedCheckboxes.length} sản phẩm)`;
    }
    
    // Disable checkout button if no items selected
    if (checkoutButton) {
        if (checkedCheckboxes.length === 0) {
            checkoutButton.disabled = true;
            checkoutButton.style.opacity = '0.5';
            checkoutButton.style.cursor = 'not-allowed';
        } else {
            checkoutButton.disabled = false;
            checkoutButton.style.opacity = '1';
            checkoutButton.style.cursor = 'pointer';
        }
    }
}

// Checkout selected items
function initCheckoutSelected() {
    const checkoutButton = document.getElementById('checkoutSelected');
    
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const checkedCheckboxes = document.querySelectorAll('.cart-item-checkbox:checked');
            
            if (checkedCheckboxes.length === 0) {
                showFlashMessage('Vui lòng chọn ít nhất một sản phẩm để thanh toán', 'warning');
                return;
            }
            
            // Get selected item IDs
            const selectedIds = Array.from(checkedCheckboxes).map(checkbox => checkbox.value);
            
            // Redirect to checkout with selected items
            const params = new URLSearchParams();
            params.set('page', 'checkout');
            params.set('selected_items', selectedIds.join(','));
            
            window.location.href = '?' + params.toString();
        });
    }
}

// Quantity controls
function initQuantityControls() {
    // Clone buttons to remove duplicate event listeners
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    // Remove existing listeners by cloning
    quantityButtons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
    });
    
    quantityInputs.forEach(input => {
        const newInput = input.cloneNode(true);
        input.parentNode.replaceChild(newInput, input);
    });
    
    // Now add fresh event listeners
    const newButtons = document.querySelectorAll('.quantity-btn');
    const newInputs = document.querySelectorAll('.quantity-input');
    
    newButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const itemId = this.dataset.itemId;
            const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            const isIncrease = this.classList.contains('quantity-increase');
            
            if (input) {
                let currentValue = parseInt(input.value) || 1;
                
                if (isIncrease) {
                    currentValue++;
                } else {
                    // If quantity is 1 and user clicks decrease, ask to remove item
                    if (currentValue === 1) {
                        e.stopImmediatePropagation();
                        if (confirm('Số lượng sẽ về 0. Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                            removeCartItem(itemId);
                        }
                        return false;
                    }
                    currentValue = Math.max(1, currentValue - 1);
                }
                
                input.value = currentValue;
                updateItemQuantity(itemId, currentValue);
            }
            
            return false;
        });
    });
    
    newInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const itemId = this.dataset.itemId;
            const quantity = Math.max(1, parseInt(this.value) || 1);
            
            this.value = quantity;
            updateItemQuantity(itemId, quantity);
            
            return false;
        });
        
        input.addEventListener('blur', function(e) {
            const itemId = this.dataset.itemId;
            const quantity = Math.max(1, parseInt(this.value) || 1);
            
            this.value = quantity;
            updateItemQuantity(itemId, quantity);
        });
    });
}

// Update cart totals (item count and total price)
function updateCartTotals() {
    const cartItems = document.querySelectorAll('.cart-item');
    let totalItems = 0;
    let totalPrice = 0;
    
    cartItems.forEach(item => {
        const quantityInput = item.querySelector('.quantity-input');
        const totalPriceElement = item.querySelector('.cart-item-total-price');
        
        if (quantityInput) {
            const quantity = parseInt(quantityInput.value) || 0;
            totalItems += quantity;
        }
        
        if (totalPriceElement) {
            const priceText = totalPriceElement.textContent.replace(/[^0-9]/g, '');
            const price = parseFloat(priceText) || 0;
            totalPrice += price;
        }
    });
    
    // Update cart summary
    const cartTotalAmount = document.getElementById('cartTotalAmount');
    const cartItemCount = document.getElementById('cartItemCount');
    
    if (cartTotalAmount) {
        cartTotalAmount.textContent = formatCurrency(totalPrice);
    }
    
    if (cartItemCount) {
        cartItemCount.textContent = `(${totalItems} sản phẩm)`;
    }
}

// Remove item functionality
function initRemoveItems() {
    const removeButtons = document.querySelectorAll('.cart-item-remove');
    
    removeButtons.forEach(button => {
        // Remove existing event listeners by cloning
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const itemId = this.dataset.itemId;
            
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(itemId);
            }
        });
    });
}

// Clear cart functionality
function initClearCart() {
    const clearCartButton = document.getElementById('clearCart');
    
    if (clearCartButton) {
        const newButton = clearCartButton.cloneNode(true);
        clearCartButton.parentNode.replaceChild(newButton, clearCartButton);
        
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm trong giỏ hàng?')) {
                clearCart();
            }
        });
    }
}

// Update item quantity via API
function updateItemQuantity(itemId, newQuantity) {
    // Show loading state
    showLoadingState(itemId);
    
    // Call API to update quantity in database
    fetch('api.php?path=cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            item_id: itemId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showFlashMessage('Đã cập nhật số lượng sản phẩm', 'success');
            // Reload page to update display
            setTimeout(() => location.reload(), 500);
        } else {
            showFlashMessage(data.message || 'Cập nhật thất bại', 'error');
            hideLoadingState(itemId);
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        showFlashMessage('Có lỗi xảy ra, vui lòng thử lại', 'error');
        hideLoadingState(itemId);
    });
}

// Remove cart item via API
function removeCartItem(itemId) {
    // Show loading state
    showLoadingState(itemId);
    
    // Call API to remove item
    fetch('api.php?path=cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            item_id: itemId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showFlashMessage('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
        } else {
            showFlashMessage(data.message || 'Xóa sản phẩm thất bại', 'error');
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        showFlashMessage('Có lỗi xảy ra, vui lòng thử lại', 'error');
    })
    .finally(() => {
        // Always reload page to show updated cart
        setTimeout(() => location.reload(), 500);
    });
}

// Update header cart count after item removal
function updateHeaderCartCountAfterRemove(newCount) {
    const cartBadge = document.getElementById('cart-count-badge');
    if (cartBadge) {
        cartBadge.textContent = newCount;
        if (newCount > 0) {
            cartBadge.style.display = 'inline-block';
        } else {
            cartBadge.style.display = 'none';
        }
    }
    
    // Update sidebar cart count if exists
    const sidebarCartCount = document.getElementById('cartCount');
    if (sidebarCartCount) {
        sidebarCartCount.textContent = newCount;
        if (newCount > 0) {
            sidebarCartCount.style.display = 'inline-flex';
        } else {
            sidebarCartCount.style.display = 'none';
        }
    }
    
    // Update dropdown count if exists
    const dropdownCount = document.querySelector('.cart-dropdown-count');
    if (dropdownCount) {
        dropdownCount.textContent = newCount + ' sản phẩm';
    }
}

// Clear entire cart
function clearCart() {
    const cartItems = document.querySelectorAll('.cart-item');
    const itemIds = Array.from(cartItems).map(item => item.dataset.itemId);
    
    // Call API for each item
    const removePromises = itemIds.map(itemId => 
        fetch('api.php?path=cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ item_id: itemId })
        })
    );
    
    Promise.all(removePromises)
        .then(() => {
            cartItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        item.remove();
                        if (index === cartItems.length - 1) {
                            showEmptyCart();
                            showFlashMessage('Đã xóa tất cả sản phẩm khỏi giỏ hàng', 'success');
                            // Update header cart count to 0
                            updateHeaderCartCountAfterRemove(0);
                            
                            // Update selected count display
                            updateSelectedCount();
                            updateCheckoutButton();
                        }
                    }, 300);
                }, index * 100);
            });
        })
        .catch(error => {
            console.error('Error clearing cart:', error);
            showFlashMessage('Có lỗi xảy ra, vui lòng thử lại', 'error');
        });
}

// Update item display after quantity change
function updateItemDisplay(itemId, newQuantity, oldQuantity) {
    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
    
    if (!cartItem) return;
    
    const totalElement = cartItem.querySelector('.cart-item-total-price');
    const checkbox = cartItem.querySelector('.cart-item-checkbox');
    
    if (!totalElement) return;
    
    // Get current total price from display
    const currentTotalText = totalElement.textContent;
    const currentTotal = parseFloat(currentTotalText.replace(/[^\d]/g, '')) || 0;
    
    // Use provided old quantity or get from input
    const quantityForCalc = oldQuantity !== undefined ? oldQuantity : newQuantity;
    
    // Calculate unit price (price per item)
    const unitPrice = quantityForCalc > 0 ? currentTotal / quantityForCalc : 0;
    
    // Calculate new total price
    const newTotalPrice = unitPrice * newQuantity;
    totalElement.textContent = formatCurrency(newTotalPrice);
    
    // Update checkbox data-price
    if (checkbox) {
        checkbox.dataset.price = newTotalPrice;
    }
    
    // Update checkout button totals
    updateCheckoutButton();
}

// Check if cart is empty and show appropriate message
function checkEmptyCart() {
    const cartItems = document.querySelectorAll('.cart-item');
    
    if (cartItems.length === 0) {
        showEmptyCart();
    }
}

// Show empty cart message
function showEmptyCart() {
    const cartContent = document.querySelector('.cart-content');
    const emptyCartHTML = `
        <div class="cart-empty">
            <div class="cart-empty-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3>Giỏ hàng trống</h3>
            <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
            <a href="?page=products" class="cart-btn cart-btn-primary">
                <i class="fas fa-shopping-bag"></i>
                Khám phá sản phẩm
            </a>
        </div>
    `;
    
    if (cartContent) {
        cartContent.innerHTML = emptyCartHTML;
    }
}

// Show loading state for specific item
function showLoadingState(itemId) {
    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
    
    if (cartItem) {
        cartItem.style.opacity = '0.6';
        cartItem.style.pointerEvents = 'none';
    }
}

// Hide loading state
function hideLoadingState(itemId) {
    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
    
    if (cartItem) {
        cartItem.style.opacity = '1';
        cartItem.style.pointerEvents = 'auto';
    }
}

// Show flash message using system flash message style
function showFlashMessage(message, type = 'info') {
    // Remove existing flash messages with same type
    const existingMessages = document.querySelectorAll(`.flash-${type}`);
    existingMessages.forEach(msg => msg.remove());
    
    // Create new flash message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `flash-message flash-${type}`;
    
    // Add icon based on type
    let iconClass = 'fa-info-circle';
    if (type === 'success') iconClass = 'fa-check-circle';
    if (type === 'error') iconClass = 'fa-exclamation-circle';
    if (type === 'warning') iconClass = 'fa-exclamation-triangle';
    
    messageDiv.innerHTML = `
        <i class="fas ${iconClass}"></i>
        <span>${message}</span>
        <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        if (messageDiv.parentElement) {
            messageDiv.style.animation = 'fadeOut 0.5s ease-out forwards';
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 500);
        }
    }, 5000);
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' VNĐ';
}

// Export functions for use in other scripts
window.CartManager = {
    updateItemQuantity,
    removeCartItem,
    clearCart,
    showFlashMessage,
    formatCurrency
};

// Handle responsive behavior
function handleResponsive() {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Adjust cart layout for mobile
        const cartContent = document.querySelector('.cart-content');
        if (cartContent) {
            cartContent.style.gridTemplateColumns = '1fr';
        }
    }
}

// Listen for window resize
window.addEventListener('resize', handleResponsive);
handleResponsive(); // Call on initial load
