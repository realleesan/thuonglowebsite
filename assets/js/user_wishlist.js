// User Wishlist JavaScript - Interactive Wishlist Management
document.addEventListener('DOMContentLoaded', function() {
    // Initialize wishlist functionality
    initWishlistFunctionality();
    
    console.log('User Wishlist JavaScript loaded successfully');
});

function initWishlistFunctionality() {
    // Remove from wishlist
    initRemoveFromWishlist();
    
    // Add to cart from wishlist
    initAddToCart();
    
    // Bulk actions
    initBulkActions();
}

// Remove from wishlist functionality
function initRemoveFromWishlist() {
    const removeButtons = document.querySelectorAll('.wishlist-remove-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                removeFromWishlist(itemId);
            }
        });
    });
}

// Add to cart functionality
function initAddToCart() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId, this);
        });
    });
}

// Bulk actions functionality
function initBulkActions() {
    const addAllToCartButton = document.getElementById('addAllToCart');
    const clearWishlistButton = document.getElementById('clearWishlist');
    
    if (addAllToCartButton) {
        addAllToCartButton.addEventListener('click', function() {
            if (confirm('Bạn có muốn thêm tất cả sản phẩm vào giỏ hàng?')) {
                addAllToCart();
            }
        });
    }
    
    if (clearWishlistButton) {
        clearWishlistButton.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm khỏi danh sách yêu thích?')) {
                clearWishlist();
            }
        });
    }
}

// Remove item from wishlist
function removeFromWishlist(itemId) {
    const wishlistItem = document.querySelector(`.wishlist-item[data-item-id="${itemId}"]`) ||
                        document.querySelector(`[data-item-id="${itemId}"]`).closest('.wishlist-item');
    
    if (wishlistItem) {
        // Show loading state
        wishlistItem.style.opacity = '0.6';
        wishlistItem.style.pointerEvents = 'none';
        
        // In a real application, this would make an API call
        setTimeout(() => {
            wishlistItem.style.transform = 'scale(0.8)';
            wishlistItem.style.opacity = '0';
            
            setTimeout(() => {
                wishlistItem.remove();
                updateWishlistCount();
                checkEmptyWishlist();
                showMessage('Đã xóa sản phẩm khỏi danh sách yêu thích', 'success');
            }, 300);
        }, 500);
    }
}

// Add item to cart
function addToCart(productId, button) {
    // Show loading state
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
    button.style.pointerEvents = 'none';
    
    // In a real application, this would make an API call
    setTimeout(() => {
        // Reset button
        button.innerHTML = originalText;
        button.style.pointerEvents = 'auto';
        
        showMessage('Đã thêm sản phẩm vào giỏ hàng thành công!', 'success');
        
        // Optionally update cart count in header
        updateCartCount();
    }, 1000);
}

// Add all items to cart
function addAllToCart() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    let completed = 0;
    
    if (addToCartButtons.length === 0) {
        showMessage('Không có sản phẩm nào để thêm vào giỏ hàng', 'info');
        return;
    }
    
    // Show bulk loading state
    const bulkActions = document.querySelector('.wishlist-bulk-actions');
    if (bulkActions) {
        bulkActions.style.opacity = '0.6';
        bulkActions.style.pointerEvents = 'none';
    }
    
    addToCartButtons.forEach((button, index) => {
        setTimeout(() => {
            const productId = button.dataset.productId;
            
            // Simulate adding to cart
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            button.style.pointerEvents = 'none';
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
                completed++;
                
                if (completed === addToCartButtons.length) {
                    // All items added
                    if (bulkActions) {
                        bulkActions.style.opacity = '1';
                        bulkActions.style.pointerEvents = 'auto';
                    }
                    
                    showMessage(`Đã thêm ${completed} sản phẩm vào giỏ hàng thành công!`, 'success');
                    updateCartCount();
                }
            }, 500);
        }, index * 200);
    });
}

// Clear entire wishlist
function clearWishlist() {
    const wishlistItems = document.querySelectorAll('.wishlist-item');
    
    if (wishlistItems.length === 0) {
        showMessage('Danh sách yêu thích đã trống', 'info');
        return;
    }
    
    wishlistItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.transform = 'scale(0.8)';
            item.style.opacity = '0';
            
            setTimeout(() => {
                item.remove();
                if (index === wishlistItems.length - 1) {
                    showEmptyWishlist();
                    showMessage('Đã xóa tất cả sản phẩm khỏi danh sách yêu thích', 'success');
                }
            }, 300);
        }, index * 100);
    });
}

// Check if wishlist is empty and show appropriate message
function checkEmptyWishlist() {
    const wishlistItems = document.querySelectorAll('.wishlist-item');
    
    if (wishlistItems.length === 0) {
        showEmptyWishlist();
    }
}

// Show empty wishlist message
function showEmptyWishlist() {
    const wishlistGrid = document.querySelector('.wishlist-grid');
    const bulkActions = document.querySelector('.wishlist-bulk-actions');
    
    if (wishlistGrid) {
        const emptyWishlistHTML = `
            <div class="wishlist-empty" style="grid-column: 1 / -1;">
                <div class="wishlist-empty-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Danh sách yêu thích trống</h3>
                <p>Bạn chưa có sản phẩm nào trong danh sách yêu thích</p>
                <a href="?page=products" class="wishlist-btn wishlist-btn-primary">
                    <i class="fas fa-shopping-bag"></i>
                    Khám phá sản phẩm
                </a>
            </div>
        `;
        
        wishlistGrid.innerHTML = emptyWishlistHTML;
    }
    
    // Hide bulk actions
    if (bulkActions) {
        bulkActions.style.display = 'none';
    }
    
    // Update header count
    updateWishlistCount();
}

// Update wishlist count in header
function updateWishlistCount() {
    const wishlistItems = document.querySelectorAll('.wishlist-item');
    const count = wishlistItems.length;
    
    // Update page title
    const pageTitle = document.querySelector('.wishlist-header-left p');
    if (pageTitle) {
        pageTitle.textContent = `Quản lý các sản phẩm bạn quan tâm (${count} sản phẩm)`;
    }
    
    // Update any wishlist count badges in header
    const wishlistBadges = document.querySelectorAll('.wishlist-count');
    wishlistBadges.forEach(badge => {
        badge.textContent = count;
    });
}

// Update cart count in header (placeholder)
function updateCartCount() {
    // This would typically make an API call to get the current cart count
    // For now, we'll just increment the displayed count
    const cartBadges = document.querySelectorAll('.cart-count');
    cartBadges.forEach(badge => {
        const currentCount = parseInt(badge.textContent) || 0;
        badge.textContent = currentCount + 1;
    });
}

// Show success/error messages
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.wishlist-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `wishlist-message wishlist-message-${type}`;
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        max-width: 300px;
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

// Export functions for use in other scripts
window.WishlistManager = {
    removeFromWishlist,
    addToCart,
    clearWishlist,
    showMessage,
    updateWishlistCount
};

// Handle responsive behavior
function handleResponsive() {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Adjust wishlist grid for mobile
        const wishlistGrid = document.querySelector('.wishlist-grid');
        if (wishlistGrid) {
            wishlistGrid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(250px, 1fr))';
        }
    }
}

// Listen for window resize
window.addEventListener('resize', handleResponsive);
handleResponsive(); // Call on initial load

// Initialize wishlist count on page load
updateWishlistCount();