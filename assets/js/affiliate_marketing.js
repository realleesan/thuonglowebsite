/**
 * Affiliate Marketing Module JavaScript
 * Xử lý logic cho Marketing tools
 */

(function() {
    'use strict';

    // ===================================
    // QR Code Functions
    // ===================================
    
    /**
     * Download QR Code as image via API
     */
    window.downloadQRCode = function() {
        const qrContainer = document.querySelector('.qr-code-container');
        const qrImage = document.querySelector('.qr-code-image');
        const affiliateCode = document.querySelector('[data-affiliate-code]')?.dataset.affiliateCode || '';
        
        if (!qrImage) {
            showAlert('Không tìm thấy QR Code', 'error');
            return;
        }
        
        // If the QR image is already a data URL or has a src, download it directly
        if (qrImage.src) {
            // Create a temporary link to download
            const link = document.createElement('a');
            link.href = qrImage.src;
            link.download = 'qr-code-' + (affiliateCode || 'affiliate') + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showAlert('Đã tải QR Code thành công', 'success');
            return;
        }
        
        // Otherwise, fetch QR code from API
        showLoading();
        
        fetch('/api/affiliate/marketing/qr-code')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success && data.qr_code_url) {
                // Create a temporary link to download
                const link = document.createElement('a');
                link.href = data.qr_code_url;
                link.download = 'qr-code-' + (affiliateCode || 'affiliate') + '.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                showAlert('Đã tải QR Code thành công', 'success');
            } else {
                showAlert('Không thể tải QR Code', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối', 'error');
            console.error('QR Code download error:', error);
        });
    };

    /**
     * Print QR Code
     */
    window.printQRCode = function() {
        const qrImage = document.querySelector('.qr-code-image');
        const affiliateCode = document.querySelector('[data-affiliate-code]')?.dataset.affiliateCode || '';
        
        if (!qrImage) {
            showAlert('Không tìm thấy QR Code', 'error');
            return;
        }
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>In QR Code - ${affiliateCode}</title>
                <style>
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        margin: 0;
                        font-family: Arial, sans-serif;
                    }
                    .print-container {
                        text-align: center;
                    }
                    img {
                        max-width: 400px;
                        height: auto;
                    }
                    h2 {
                        margin-top: 20px;
                        color: #333;
                    }
                    .affiliate-code {
                        margin-top: 10px;
                        font-size: 18px;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <img src="${qrImage.src}" alt="QR Code">
                    <h2>Quét để truy cập link giới thiệu</h2>
                    <p class="affiliate-code">Mã giới thiệu: ${affiliateCode}</p>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        
        setTimeout(() => {
            printWindow.print();
        }, 500);
    };
    
    /**
     * Copy referral link to clipboard
     */
    window.copyReferralLink = function() {
        const referralLink = document.querySelector('[data-referral-link]')?.dataset.referralLink || 
                            document.getElementById('referralLink')?.value;
        
        if (!referralLink) {
            showAlert('Không tìm thấy link giới thiệu', 'error');
            return;
        }
        
        navigator.clipboard.writeText(referralLink)
        .then(() => {
            showAlert('Đã sao chép link giới thiệu', 'success');
        })
        .catch(err => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = referralLink;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showAlert('Đã sao chép link giới thiệu', 'success');
            } catch (e) {
                showAlert('Không thể sao chép', 'error');
            }
            document.body.removeChild(textArea);
        });
    };
    
    /**
     * Share to social media
     */
    window.shareToFacebook = function() {
        const referralLink = document.querySelector('[data-referral-link]')?.dataset.referralLink || '';
        if (referralLink) {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink)}`, '_blank');
        }
    };
    
    window.shareToZalo = function() {
        const referralLink = document.querySelector('[data-referral-link]')?.dataset.referralLink || '';
        const title = document.querySelector('[data-share-title]')?.dataset.shareTitle || 'Tham gia cùng tôi';
        if (referralLink) {
            window.open(`https://zalo.me/share?url=${encodeURIComponent(referralLink)}&title=${encodeURIComponent(title)}`, '_blank');
        }
    };
    
    /**
     * Generate new referral code via API
     */
    window.generateNewReferralCode = function() {
        if (!confirm('Bạn có chắc muốn tạo mã giới thiệu mới? Mã cũ sẽ không còn hiệu lực.')) {
            return;
        }
        
        showLoading();
        
        fetch('/api/affiliate/marketing/referral-code/regenerate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success && data.new_code) {
                // Update UI with new code
                const codeElements = document.querySelectorAll('[data-affiliate-code]');
                codeElements.forEach(el => {
                    el.dataset.affiliateCode = data.new_code;
                    if (el.tagName === 'INPUT') {
                        el.value = data.new_code;
                    } else {
                        el.textContent = data.new_code;
                    }
                });
                
                // Update referral link
                const linkElements = document.querySelectorAll('[data-referral-link]');
                linkElements.forEach(el => {
                    el.dataset.referralLink = data.new_referral_link;
                    if (el.tagName === 'INPUT') {
                        el.value = data.new_referral_link;
                    }
                });
                
                showAlert('Đã tạo mã giới thiệu mới: ' + data.new_code, 'success');
            } else {
                showAlert(data.message || 'Không thể tạo mã mới', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối', 'error');
            console.error('Generate code error:', error);
        });
    };

    // ===================================
    // Initialize
    // ===================================
    console.log('Marketing Module Initialized');

})();
