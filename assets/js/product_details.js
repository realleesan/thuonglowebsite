/**
 * Product Details Page JavaScript
 */

function addToCart(productId, quantity = 1) {
    fetch('api.php?path=cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.require_login) {
            window.location.href = '?page=login&redirect=' + encodeURIComponent(window.location.href);
        } else if (data.success) {
            alert(data.message);
            updateCartCount();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại');
    });
}

function buyNow(productId, quantity = 1) {
    fetch('api.php?path=cart/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.require_login) {
            window.location.href = '?page=login&redirect=' + encodeURIComponent(window.location.href);
        } else if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại');
    });
}

function updateCartCount() {
    const cartBadge = document.getElementById('cart-count-badge');
    if (!cartBadge) return;
    
    fetch('api.php?action=getUserData')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                const count = data.cart.length;
                cartBadge.textContent = count;
                if (count > 0) {
                    cartBadge.style.display = 'inline-block';
                } else {
                    cartBadge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanels = document.querySelectorAll('.tab-panel');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding panel
            const tabId = this.getAttribute('data-tab');
            const targetPanel = document.getElementById(tabId);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
        });
    });
});
