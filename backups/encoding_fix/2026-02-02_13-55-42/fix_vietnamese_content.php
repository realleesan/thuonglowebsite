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
    'hï¿½ng vï¿½ dá»‹ch vá»¥' => 'hÃ ng vÃ  dá»‹ch vá»¥',
    'xuyï¿½n biï¿½n giá»›i' => 'xuyÃªn biÃªn giá»›i',
    'ThÆ°Æ¡ng máº¡i xuyï¿½n biï¿½n giá»›i' => 'ThÆ°Æ¡ng máº¡i xuyÃªn biÃªn giá»›i',
    'Ná»n táº£ng data nguá»“n hï¿½ng vï¿½ dá»‹ch vá»¥' => 'Ná»n táº£ng data nguá»“n hÃ ng vÃ  dá»‹ch vá»¥',
    'cháº¥t lï¿½ng' => 'cháº¥t lÆ°á»£ng',
    'cung cï¿½p' => 'cung cáº¥p',
    'nhï¿½ cung cï¿½p' => 'nhÃ  cung cáº¥p',
    'thï¿½ trÆ°á»ng' => 'thá»‹ trÆ°á»ng',
    'doanh nghiï¿½p' => 'doanh nghiá»‡p',
    'phï¿½t triï¿½n' => 'phÃ¡t triá»ƒn',
    'chï¿½nh ngï¿½ch' => 'chÃ­nh ngáº¡ch',
    'trï¿½n gï¿½i' => 'trá»n gÃ³i',
    'quï¿½c tï¿½' => 'quá»‘c táº¿',
    'Ä‘ï¿½nh hï¿½ng' => 'Ä‘Ã¡nh hÃ ng',
    'phiï¿½n dï¿½ch' => 'phiÃªn dá»‹ch',
    'hï¿½ trï¿½' => 'há»— trá»£',
    'tÆ° vï¿½n' => 'tÆ° váº¥n',
    'kiï¿½m tra' => 'kiá»ƒm tra',
    'Ä‘ï¿½ng kï¿½' => 'Ä‘Äƒng kÃ½',
    'Ä‘ï¿½ng nhï¿½p' => 'Ä‘Äƒng nháº­p',
    'liï¿½n hï¿½' => 'liÃªn há»‡',
    'giï¿½i thiï¿½u' => 'giá»›i thiá»‡u',
    'hÆ°á»›ng dï¿½n' => 'hÆ°á»›ng dáº«n',
    'cï¿½ch Ä‘ï¿½t hï¿½ng' => 'cÃ¡ch Ä‘áº·t hÃ ng',
    'thanh toï¿½n' => 'thanh toÃ¡n',
    'vï¿½n chuyï¿½n' => 'váº­n chuyá»ƒn',
    'cï¿½u hï¿½i' => 'cÃ¢u há»i',
    'thÆ°á»ng gï¿½p' => 'thÆ°á»ng gáº·p',
    'tin tï¿½c' => 'tin tá»©c',
    'chï¿½nh sï¿½ch' => 'chÃ­nh sÃ¡ch',
    'hï¿½i quan' => 'háº£i quan',
    'kinh nghiï¿½m' => 'kinh nghiá»‡m',
    'kinh doanh' => 'kinh doanh',
    'Ä‘ï¿½i lï¿½' => 'Ä‘áº¡i lÃ½',
    'sáº£n phï¿½m' => 'sáº£n pháº©m',
    'danh mï¿½c' => 'danh má»¥c',
    'nï¿½i bï¿½t' => 'ná»•i báº­t',
    'chi tiï¿½t' => 'chi tiáº¿t',
    'giï¿½ cï¿½' => 'giÃ¡ cáº£',
    'cï¿½nh tranh' => 'cáº¡nh tranh',
    'Trung Quï¿½c' => 'Trung Quá»‘c',
    'Thï¿½i Lan' => 'ThÃ¡i Lan',
    'uy tï¿½n' => 'uy tÃ­n',
    'an toï¿½n' => 'an toÃ n',
    'nhanh chï¿½ng' => 'nhanh chÃ³ng',
    'chuyï¿½n nghiï¿½p' => 'chuyÃªn nghiá»‡p',
    'chuyï¿½n gia' => 'chuyÃªn gia',
    'linh hoï¿½t' => 'linh hoáº¡t',
    'hiï¿½u quï¿½' => 'hiá»‡u quáº£',
    'tá»· giï¿½' => 'tá»· giÃ¡',
    'Æ°u Ä‘ï¿½i' => 'Æ°u Ä‘Ã£i',
    'khï¿½m phï¿½' => 'khÃ¡m phÃ¡',
    'chï¿½o má»«ng' => 'chÃ o má»«ng',
    'hï¿½ng Ä‘ï¿½u' => 'hÃ ng Ä‘áº§u',
    'miá»…n phï¿½' => 'miá»…n phÃ­',
    'xem thï¿½m' => 'xem thÃªm',
    'táº¥t cï¿½' => 'táº¥t cáº£',
    'hiï¿½n thï¿½' => 'hiá»ƒn thá»‹',
    'kï¿½t quï¿½' => 'káº¿t quáº£',
    'má»›i nhï¿½t' => 'má»›i nháº¥t',
    'giï¿½ cao' => 'giÃ¡ cao',
    'giï¿½ thï¿½p' => 'giÃ¡ tháº¥p',
    'phï¿½ biï¿½n' => 'phá»• biáº¿n',
    'Ä‘ï¿½nh giï¿½' => 'Ä‘Ã¡nh giÃ¡',
    'trung bï¿½nh' => 'trung bÃ¬nh',
    'tÃ¬m kiï¿½m' => 'tÃ¬m kiáº¿m',
    'nguá»“n hï¿½ng' => 'nguá»“n hÃ ng',
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
        echo "âš ï¸  File not found: $file\n";
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
            echo "âœ… Fixed $file ($fileFixed replacements)\n";
            $filesFixed++;
            $totalFixed += $fileFixed;
        } else {
            echo "âŒ Failed to save $file\n";
        }
    } else {
        echo "âœ… $file - No issues found\n";
    }
}

echo "\n=== Fix Summary ===\n";
echo "Files processed: " . count($filesToFix) . "\n";
echo "Files fixed: $filesFixed\n";
echo "Total replacements: $totalFixed\n";

if ($totalFixed > 0) {
    echo "\nâœ… Vietnamese content has been fixed!\n";
    echo "Please upload the fixed files to hosting.\n";
} else {
    echo "\nâœ… No corrupted content found in files.\n";
    echo "The issue might be elsewhere.\n";
}