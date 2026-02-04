<?php
// Set proper HTTP status code
http_response_code(404);

// Get URL builder for navigation links
global $urlBuilder;
if (!isset($urlBuilder)) {
    require_once 'core/functions.php';
    init_url_builder();
}
?>

<div class="error-page-container">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1 class="error-title">404 - KhÃ´ng tÃ¬m tháº¥y trang</h1>
        
        <p class="error-message">
            Xin lá»—i, trang báº¡n Ä‘ang tÃ¬m kiáº¿m khÃ´ng tá»“n táº¡i hoáº·c Ä‘Ã£ Ä‘Æ°á»£c di chuyá»ƒn.
        </p>
        
        <div class="error-suggestions">
            <h3>Báº¡n cÃ³ thá»ƒ:</h3>
            <ul>
                <li>Kiá»ƒm tra láº¡i Ä‘Æ°á»ng dáº«n URL</li>
                <li>Quay vá» <a href="<?php echo nav_url('home'); ?>">trang chá»§</a></li>
                <li>Xem <a href="<?php echo nav_url('products'); ?>">sáº£n pháº©m</a> cá»§a chÃºng tÃ´i</li>
                <li>Äá»c <a href="<?php echo nav_url('news'); ?>">tin tá»©c</a> má»›i nháº¥t</li>
                <li><a href="<?php echo nav_url('contact'); ?>">LiÃªn há»‡</a> vá»›i chÃºng tÃ´i náº¿u cáº§n há»— trá»£</li>
            </ul>
        </div>
        
        <div class="error-actions">
            <a href="<?php echo nav_url('home'); ?>" class="btn btn-primary">
                <i class="fas fa-home"></i> Vá» trang chá»§
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay láº¡i
            </a>
        </div>
    </div>
</div>

<style>
.error-page-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.error-content {
    max-width: 600px;
    margin: 0 auto;
}

.error-icon {
    font-size: 4rem;
    color: #ffc107;
    margin-bottom: 20px;
}

.error-title {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
}

.error-message {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.error-suggestions {
    text-align: left;
    margin: 30px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.error-suggestions h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.error-suggestions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-suggestions li {
    padding: 5px 0;
    position: relative;
    padding-left: 20px;
}

.error-suggestions li:before {
    content: "â†’";
    position: absolute;
    left: 0;
    color: #007bff;
    font-weight: bold;
}

.error-suggestions a {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.error-suggestions a:hover {
    color: #0056b3;
    text-decoration: underline;
}

.error-actions {
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    margin: 0 10px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
    transform: translateY(-2px);
}

.btn i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .error-title {
        font-size: 2rem;
    }
    
    .error-icon {
        font-size: 3rem;
    }
    
    .btn {
        display: block;
        margin: 10px 0;
        width: 100%;
    }
}
</style>

<script>
// Log 404 error for analytics (optional)
console.log('404 Error - Page not found:', window.location.href);

// Optional: Send 404 error to analytics service
// gtag('event', 'page_not_found', {
//     'page_location': window.location.href,
//     'page_title': '404 - Page Not Found'
// });
</script>