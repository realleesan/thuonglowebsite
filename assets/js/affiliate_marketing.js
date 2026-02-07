/**
 * Affiliate Marketing Module JavaScript
 * Xử lý logic cho Marketing tools
 */

(function() {
    'use strict';

    // ===================================
    // QR Code Functions
    // ===================================
    window.downloadQRCode = function() {
        showAlert('Tính năng tải QR Code đang được phát triển', 'info');
    };

    window.printQRCode = function() {
        const qrImage = document.querySelector('.qr-code-image');
        if (!qrImage) return;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>In QR Code</title>
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
                </style>
            </head>
            <body>
                <div class="print-container">
                    <img src="${qrImage.src}" alt="QR Code">
                    <h2>Quét để truy cập link giới thiệu</h2>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        
        setTimeout(() => {
            printWindow.print();
        }, 500);
    };

    // ===================================
    // Initialize
    // ===================================
    console.log('Marketing Module Initialized');

})();
