// User Cart JavaScript - Interactive Shopping Cart
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart functionality
    initCartFunctionality();
    
    console.log('User Cart JavaScript loaded successfully');
});

function initCartFunctionality() {
    // Quantity controls
    initQuantityControls();
    
    // Remove item functionality
    initRemoveItems();
    
    // Clear cart functionality
    initClearCart();
    
    // Update totals
    updateCartTotals();
}

// Quantity controls
function initQuantityControls() {
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            const isIncrease = this.classList.contains('quantity-increase');
            
            if (input) {
                let currentValue = parseInt(input.value) || 1;
                
                if (isIncrease) {
                    currentValue++;
                } else {
                    currentValue = Math.max(1, currentValue - 1);
                }
                
                input.value = currentValue;
                updateItemQuantity(itemId, currentValue);
            }
        });
    });
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const quantity = Math.max(1, parseInt(this.value) || 1);
            
            this.value = quantity;
            updateItemQuantity(itemId, quantity);
        });
    });
}

// Remove item functionality
function initRemoveItems() {
    const removeButtons = document.querySelectorAll('.cart-item-remove');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
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
        clearCartButton.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm trong giỏ hàng?')) {
                clearCart();
            }
        });
    }
}

// Update item quantity
function updateItemQuantity(itemId, quantity) {
    // Show loading state
    showLoadingState(itemId);
    
    // In a real application, this would make an API call
    // For now, we'll simulate the update
    setTimeout(() => {
        updateItemDisplay(itemId, quantity);
        updateCartTotals();
        hideLoadingState(itemId);
        showMessage('Đã cập nhật số lượng sản phẩm', 'success');
    }, 500);
}

// Remove cart item
function removeCartItem(itemId) {
    // Show loading state
    showLoadingState(itemId);
    
    // In a real application, this would make an API call
    setTimeout(() => {
        const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`) ||
                        document.querySelector(`[data-item-id="${itemId}"]`).closest('.cart-item');
        
        if (cartItem) {
            cartItem.style.opacity = '0';
            cartItem.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                cartItem.remove();
                updateCartTotals();
                checkEmptyCart();
                showMessage('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
            }, 300);
        }
    }, 500);
}

// Clear entire cart
function clearCart() {
    const cartItems = document.querySelectorAll('.cart-item');
    
    cartItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                item.remove();
                if (index === cartItems.length - 1) {
                    showEmptyCart();
                    showMessage('Đã xóa tất cả sản phẩm khỏi giỏ hàng', 'success');
                }
            }, 300);
        }, index * 100);
    });
}

// Update item display after quantity change
function updateItemDisplay(itemId, quantity) {
    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`) ||
                    document.querySelector(`[data-item-id="${itemId}"]`).closest('.cart-item');
    
    if (cartItem) {
        const priceElement = cartItem.querySelector('.cart-item-unit-price');
        const totalElement = cartItem.querySelector('.cart-item-total-price');
        
        if (priceElement && totalElement) {
            const unitPrice = parseFloat(priceElement.textContent.replace(/[^\d]/g, ''));
            const totalPrice = unitPrice * quantity;
            
            totalElement.textContent = formatCurrency(totalPrice);
        }
    }
}

// Update cart totals
function updateCartTotals() {
    const cartItems = document.querySelectorAll('.cart-item');
    let totalItems = 0;
    let totalAmount = 0;
    
    cartItems.forEach(item => {
        const quantityInput = item.querySelector('.quantity-input');
        const totalPriceElement = item.querySelector('.cart-item-total-price');
        
        if (quantityInput && totalPriceElement) {
            const quantity = parseInt(quantityInput.value) || 0;
            const itemTotal = parseFloat(totalPriceElement.textContent.replace(/[^\d]/g, ''));
            
            totalItems += quantity;
            totalAmount += itemTotal;
        }
    });
    
    // Update summary display
    const totalItemsElement = document.querySelector('.cart-summary-row span:contains("sản phẩm")');
    const totalAmountElements = document.querySelectorAll('.cart-summary-row span:last-child');
    
    if (totalItemsElement) {
        totalItemsElement.textContent = `${totalItems} sản phẩm`;
    }
    
    totalAmountElements.forEach(element => {
        if (element.textContent.includes('VNĐ')) {
            element.textContent = formatCurrency(totalAmount);
        }
    });
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
    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`) ||
                    document.querySelector(`[data-item-id="${itemId}"]`).closest('.cart-item');
    
    if (cartItem) {
        cartItem.style.opacity = '0.6';
        cartItem.style.pointerEvents = 'none';
    }
}

// Hide loading state
function hideLoadingState(itemId) {
    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`) ||
                    document.querySelector(`[data-item-id="${itemId}"]`).closest('.cart-item');
    
    if (cartItem) {
        cartItem.style.opacity = '1';
        cartItem.style.pointerEvents = 'auto';
    }
}

// Show success/error messages
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.cart-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `cart-message cart-message-${type}`;
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10B981' : '#EF4444'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.opacity = '1';
    }, 100);
    
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(messageDiv)) {
                document.body.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
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
    showMessage,
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