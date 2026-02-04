<?php
/**
 * Fix Vietnamese Content Script
 * Thuong Lo Website - Fix corrupted Vietnamese characters
 */

echo "=== Fix Vietnamese Content ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// Define correct Vietnamese text replacements
$fixes = [
    // Common corrupted patterns and their fixes
    'h�ng v� dịch vụ' => 'hàng và dịch vụ',
    'xuy�n bi�n giới' => 'xuyên biên giới',
    'Thương mại xuy�n bi�n giới' => 'Thương mại xuyên biên giới',
    'Nền tảng data nguồn h�ng v� dịch vụ' => 'Nền tảng data nguồn hàng và dịch vụ',
    'chất l�ng' => 'chất lượng',
    'cung c�p' => 'cung cấp',
    'nh� cung c�p' => 'nhà cung cấp',
    'th� trường' => 'thị trường',
    'doanh nghi�p' => 'doanh nghiệp',
    'ph�t tri�n' => 'phát triển',
    'ch�nh ng�ch' => 'chính ngạch',
    'tr�n g�i' => 'trọn gói',
    'qu�c t�' => 'quốc tế',
    'đ�nh h�ng' => 'đánh hàng',
    'phi�n d�ch' => 'phiên dịch',
    'h� tr�' => 'hỗ trợ',
    'tư v�n' => 'tư vấn',
    'ki�m tra' => 'kiểm tra',
    'đ�ng k�' => 'đăng ký',
    'đ�ng nh�p' => 'đăng nhập',
    'li�n h�' => 'liên hệ',
    'gi�i thi�u' => 'giới thiệu',
    'hướng d�n' => 'hướng dẫn',
    'c�ch đ�t h�ng' => 'cách đặt hàng',
    'thanh to�n' => 'thanh toán',
    'v�n chuy�n' => 'vận chuyển',
    'c�u h�i' => 'câu hỏi',
    'thường g�p' => 'thường gặp',
    'tin t�c' => 'tin tức',
    'ch�nh s�ch' => 'chính sách',
    'h�i quan' => 'hải quan',
    'kinh nghi�m' => 'kinh nghiệm',
    'kinh doanh' => 'kinh doanh',
    'đ�i l�' => 'đại lý',
    'sản ph�m' => 'sản phẩm',
    'danh m�c' => 'danh mục',
    'n�i b�t' => 'nổi bật',
    'chi ti�t' => 'chi tiết',
    'gi� c�' => 'giá cả',
    'c�nh tranh' => 'cạnh tranh',
    'Trung Qu�c' => 'Trung Quốc',
    'Th�i Lan' => 'Thái Lan',
    'uy t�n' => 'uy tín',
    'an to�n' => 'an toàn',
    'nhanh ch�ng' => 'nhanh chóng',
    'chuy�n nghi�p' => 'chuyên nghiệp',
    'chuy�n gia' => 'chuyên gia',
    'linh ho�t' => 'linh hoạt',
    'hi�u qu�' => 'hiệu quả',
    'tỷ gi�' => 'tỷ giá',
    'ưu đ�i' => 'ưu đãi',
    'kh�m ph�' => 'khám phá',
    'ch�o mừng' => 'chào mừng',
    'h�ng đ�u' => 'hàng đầu',
    'miễn ph�' => 'miễn phí',
    'xem th�m' => 'xem thêm',
    'tất c�' => 'tất cả',
    'hi�n th�' => 'hiển thị',
    'k�t qu�' => 'kết quả',
    'mới nh�t' => 'mới nhất',
    'gi� cao' => 'giá cao',
    'gi� th�p' => 'giá thấp',
    'ph� bi�n' => 'phổ biến',
    'đ�nh gi�' => 'đánh giá',
    'trung b�nh' => 'trung bình',
    'tìm ki�m' => 'tìm kiếm',
    'nguồn h�ng' => 'nguồn hàng',
];

// Files to fix (based on the pages that have issues)
$filesToFix = [
    'app/views/home/home.php',
    'app/views/about/about.php', 
    'app/views/contact/contact.php',
    'app/views/auth/auth.php',
    'app/views/auth/login.php',
    'app/views/auth/register.php',
    'app/views/auth/forgot.php',
    'app/views/payment/payment.php',
    'app/views/payment/checkout.php',
    'app/views/payment/success.php',
    'app/views/_layout/header.php',
    'app/views/_layout/footer.php',
    'app/views/_layout/cta.php',
];

$totalFixed = 0;
$filesFixed = 0;

foreach ($filesToFix as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileFixed = 0;
    
    // Apply fixes
    foreach ($fixes as $corrupted => $correct) {
        $count = 0;
        $content = str_replace($corrupted, $correct, $content, $count);
        if ($count > 0) {
            $fileFixed += $count;
            echo "  Fixed '$corrupted' -> '$correct' ($count times)\n";
        }
    }
    
    // Save if changes were made
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Fixed $file ($fileFixed replacements)\n";
            $filesFixed++;
            $totalFixed += $fileFixed;
        } else {
            echo "❌ Failed to save $file\n";
        }
    } else {
        echo "✅ $file - No issues found\n";
    }
}

echo "\n=== Fix Summary ===\n";
echo "Files processed: " . count($filesToFix) . "\n";
echo "Files fixed: $filesFixed\n";
echo "Total replacements: $totalFixed\n";

if ($totalFixed > 0) {
    echo "\n✅ Vietnamese content has been fixed!\n";
    echo "Please upload the fixed files to hosting.\n";
} else {
    echo "\n✅ No corrupted content found in files.\n";
    echo "The issue might be elsewhere.\n";
}