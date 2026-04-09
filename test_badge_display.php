<?php
/**
 * Test file for debugging badge display issues
 * Access: https://test1.web3b.com/test_badge_display.php
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Badge Display</title>
    
    <!-- Load all affiliate CSS files in same order as master layout -->
    <link rel="stylesheet" href="assets/css/affiliate_style.css">
    <link rel="stylesheet" href="assets/css/affiliate_header.css">
    <link rel="stylesheet" href="assets/css/affiliate_components.css">
    <link rel="stylesheet" href="assets/css/affiliate_responsive.css">
    <link rel="stylesheet" href="assets/css/affiliate_customers.css">
    
    <style>
        /* Debug styles */
        .debug-section {
            margin: 20px;
            padding: 20px;
            border: 2px solid #ccc;
        }
        .debug-info {
            background: #f0f0f0;
            padding: 10px;
            margin-bottom: 20px;
            font-family: monospace;
        }
        /* Highlight elements for debugging */
        .inspect-borders * {
            outline: 1px dashed red;
        }
        .inspect-borders .badge {
            outline: 2px solid blue !important;
        }
        .inspect-borders td {
            outline: 2px solid green !important;
        }
    </style>
</head>
<body>
    <h1>Debug: Badge Display Test</h1>
    
    <div class="debug-section">
        <h2>Test 1: Badge Classes in Table (Standard)</h2>
        <div class="debug-info">
            Table with exact classes from list.php
        </div>
        <table class="customers-table">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Đơn hàng</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Test User</td>
                    <td data-label="Đơn hàng">
                        <div class="customer-orders">
                            <span class="badge badge-info">
                                5 đơn
                            </span>
                        </div>
                    </td>
                    <td data-label="Trạng thái">
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle"></i>
                            Hoạt động
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Test User 2</td>
                    <td data-label="Đơn hàng">
                        <div class="customer-orders">
                            <span class="badge badge-info">
                                0 đơn
                            </span>
                        </div>
                    </td>
                    <td data-label="Trạng thái">
                        <span class="badge badge-secondary">
                            <i class="fas fa-pause-circle"></i>
                            Không hoạt động
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="debug-section inspect-borders">
        <h2>Test 2: With Border Inspection (See Element Boundaries)</h2>
        <div class="debug-info">
            Red = all elements, Blue = badge, Green = td
        </div>
        <table class="customers-table">
            <tbody>
                <tr>
                    <td data-label="Đơn hàng">
                        <div class="customer-orders">
                            <span class="badge badge-info">3 đơn</span>
                        </div>
                    </td>
                    <td data-label="Trạng thái">
                        <span class="badge badge-success">Hoạt động</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="debug-section">
        <h2>Test 3: Badge Outside Table (Control)</h2>
        <div class="debug-info">
            Testing badges outside table context
        </div>
        <span class="badge badge-info">Info Badge</span>
        <span class="badge badge-success">Success Badge</span>
        <span class="badge badge-secondary">Secondary Badge</span>
    </div>
    
    <div class="debug-section">
        <h2>Test 4: Computed Styles Check</h2>
        <div class="debug-info">
            Right-click → Inspect → Check computed styles for the badges above
        </div>
        <p>Check these CSS properties:</p>
        <ul>
            <li><strong>color</strong> - Should be dark (not white or transparent)</li>
            <li><strong>display</strong> - Should be inline-flex or inline-block</li>
            <li><strong>visibility</strong> - Should be visible</li>
            <li><strong>opacity</strong> - Should be 1</li>
            <li><strong>font-size</strong> - Should not be 0</li>
        </ul>
    </div>
    
    <div class="debug-section">
        <h2>Test 5: Inline Debug Styles</h2>
        <table class="customers-table">
            <tbody>
                <tr>
                    <td data-label="Đơn hàng">
                        <div class="customer-orders">
                            <span class="badge badge-info" style="color: red !important; background: yellow !important; border: 2px solid black;">
                                FORCED VISIBLE
                            </span>
                        </div>
                    </td>
                    <td data-label="Trạng thái">
                        <span class="badge badge-success" style="color: blue !important; background: pink !important; border: 2px solid black;">
                            FORCED VISIBLE
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p>If the badges above show with red/yellow and blue/pink colors, then CSS is being overridden somewhere.</p>
    </div>

    <!-- Test 6: Extreme Force Test -->
    <div class="debug-section" style="background: #333;">
        <h2 style="color: white;">Test 6: Extreme Force (Dark BG)</h2>
        <div class="debug-info">
            Badge với font-size cố định 16px, line-height 1.5
        </div>
        <span class="badge badge-info" style="
            color: #fff !important;
            background: #ff0000 !important;
            font-size: 16px !important;
            line-height: 1.5 !important;
            padding: 10px 20px !important;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            transform: none !important;
            text-indent: 0 !important;
            overflow: visible !important;
            white-space: normal !important;
            min-width: 100px;
            min-height: 30px;
        ">MUST BE VISIBLE</span>
    </div>
    
    <!-- Test 7: Raw Text Test -->
    <div class="debug-section">
        <h2>Test 7: Raw Text Comparison</h2>
        <table>
            <tr>
                <td style="border: 1px solid black; padding: 10px;">
                    <span style="font-size: 14px; color: black;">Raw text without badge class</span>
                </td>
                <td style="border: 1px solid black; padding: 10px;">
                    <span class="badge" style="border: 2px solid red;">Text with badge class only</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Test 8: Progressive Class Test -->
    <div class="debug-section">
        <h2>Test 8: Progressive Class Test</h2>
        <table>
            <tr>
                <td style="border: 1px solid black; padding: 10px;">
                    <span style="color: black;">1. No class</span>
                </td>
                <td style="border: 1px solid black; padding: 10px;">
                    <span class="badge" style="color: red;">2. Only .badge</span>
                </td>
                <td style="border: 1px solid black; padding: 10px;">
                    <span class="badge badge-info">3. .badge.badge-info</span>
                </td>
                <td style="border: 1px solid black; padding: 10px;">
                    <span class="mybadge" style="display:inline-flex;padding:4px 8px;font-size:12px;background:#DBEAFE;color:#1E40AF;border-radius:6px;">4. Custom class</span>
                </td>
            </tr>
        </table>
    </div>

    <script>
        // Auto-log computed styles for debugging
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== Badge Debug Info ===');
            
            // Get all test elements
            const test1 = document.querySelector('td span:not([class])');
            const test2 = document.querySelector('td .badge');
            const test3 = document.querySelector('td .badge-info');
            const test4 = document.querySelector('td .mybadge');
            
            [test1, test2, test3, test4].forEach((el, index) => {
                if (!el) return;
                const computed = window.getComputedStyle(el);
                const rect = el.getBoundingClientRect();
                console.log(`Test ${index + 1} (${el.className || 'no class'}):`, {
                    text: el.textContent.trim().substring(0, 30),
                    color: computed.color,
                    backgroundColor: computed.backgroundColor,
                    display: computed.display,
                    visibility: computed.visibility,
                    opacity: computed.opacity,
                    fontSize: computed.fontSize,
                    lineHeight: computed.lineHeight,
                    height: rect.height,
                    width: rect.width,
                    classList: el.classList.toString()
                });
            });
            
            // Check if .badge is being overridden
            const badgeRule = getCSSRule('.badge');
            console.log('.badge CSS rule:', badgeRule ? badgeRule.cssText.substring(0, 200) : 'Not found');
        });
        
        function getCSSRule(selector) {
            for (let sheet of document.styleSheets) {
                try {
                    for (let rule of sheet.cssRules) {
                        if (rule.selectorText === selector) {
                            return rule;
                        }
                    }
                } catch (e) {}
            }
            return null;
        }
    </script>
</body>
</html>
