// Wishlist JavaScript - Toggle, Check, Clear All

// Store wishlist product IDs
var wishlistProductIds = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    checkWishlistStatus();
});

// Get product ID from button
function getProductIdFromButton(button) {
    var onclick = button.getAttribute('onclick');
    if (onclick) {
        var match = onclick.match(/toggleWishlist\((\d+)/);
        if (match) {
            return parseInt(match[1]);
        }
        match = onclick.match(/removeFromWishlist\((\d+)/);
        if (match) {
            return parseInt(match[1]);
        }
        match = onclick.match(/addToCartFromWishlist\((\d+)/);
        if (match) {
            return parseInt(match[1]);
        }
    }
    return null;
}

// Check wishlist status from server
function checkWishlistStatus() {
    console.log('Checking wishlist status...');
    fetch('api.php?action=wishlist/check', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        console.log('Wishlist check response:', data);
        if (data.success && data.wishlist_products) {
            wishlistProductIds = data.wishlist_products;
            console.log('Wishlist product IDs:', wishlistProductIds);
            updateWishlistIcons();
        }
    })
    .catch(function(error) {
        console.error('Error checking wishlist:', error);
    });
}

// Update all wishlist icons based on current status
function updateWishlistIcons() {
    var buttons = document.querySelectorAll('.wishlist-icon-btn');
    buttons.forEach(function(button) {
        var productId = getProductIdFromButton(button);
        if (productId && wishlistProductIds.includes(productId)) {
            var icon = button.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-heart';
            }
            button.classList.add('active');
            button.setAttribute('title', 'Xóa khỏi yêu thích');
        }
    });
}

// Toggle wishlist - add or remove
function toggleWishlist(productId, button) {
    console.log('Toggling wishlist for product:', productId);
    if (!button) {
        button = document.querySelector('.wishlist-icon-btn[onclick*="' + productId + '"]');
    }
    
    if (button) {
        button.style.pointerEvents = 'none';
    }
    
    var isInWishlist = wishlistProductIds.includes(productId);
    console.log('Is in wishlist:', isInWishlist);
    var action = isInWishlist ? 'wishlist/remove' : 'wishlist/add';
    
    fetch('api.php?action=' + action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId }),
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        console.log('Toggle response:', data);
        if (data.success) {
            // Update local array
            if (isInWishlist) {
                wishlistProductIds = wishlistProductIds.filter(function(id) { return id !== productId; });
            } else {
                wishlistProductIds.push(productId);
            }
            console.log('Updated wishlistProductIds:', wishlistProductIds);
            
            // Update button appearance
            var icon = button.querySelector('i');
            if (icon) {
                if (isInWishlist) {
                    icon.className = 'far fa-heart';
                    button.classList.remove('active');
                    button.setAttribute('title', 'Thêm vào yêu thích');
                } else {
                    icon.className = 'fas fa-heart';
                    button.classList.add('active');
                    button.setAttribute('title', 'Xóa khỏi yêu thích');
                }
            }
            
            alert(data.message);
        } else if (data.require_login) {
            alert('Vui lòng đăng nhập để thêm vào yêu thích');
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    })
    .finally(function() {
        if (button) {
            button.style.pointerEvents = 'auto';
        }
    });
}

// Remove single item from wishlist page
function removeFromWishlist(productId, button) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi yêu thích?')) {
        return;
    }
    
    fetch('api.php?action=wishlist/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId }),
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            alert(data.message);
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

// Add to cart from wishlist page
function addToCartFromWishlist(productId, button) {
    fetch('api.php?action=cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            product_id: productId,
            quantity: 1
        }),
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            alert(data.message || 'Đã thêm vào giỏ hàng');
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

// Clear all wishlist
function clearAllWishlist() {
    if (!confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi yêu thích?')) {
        return;
    }
    
    fetch('api.php?action=wishlist/clear', {
        method: 'POST',
        credentials: 'same-origin'
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            alert(data.message);
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}
