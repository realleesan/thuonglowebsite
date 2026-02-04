<?php
require_once 'auth.php';

// Xá»­ lÃ½ Ä‘Äƒng kÃ½
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    $confirmPassword = sanitize($_POST['confirm_password'] ?? '');
    $refCode = sanitize($_POST['ref_code'] ?? '');
    
    // Validation
    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Vui lÃ²ng nháº­p Ä‘áº§y Ä‘á»§ thÃ´ng tin báº¯t buá»™c';
    } elseif ($password !== $confirmPassword) {
        $error = 'Máº­t kháº©u xÃ¡c nháº­n khÃ´ng khá»›p';
    } elseif (strlen($password) < 6) {
        $error = 'Máº­t kháº©u pháº£i cÃ³ Ã­t nháº¥t 6 kÃ½ tá»±';
    } else {
        // MÃ´ phá»ng Ä‘Äƒng kÃ½ - luÃ´n thÃ nh cÃ´ng
        if (mockRegister($fullName, $email, $phone, $password, $refCode)) {
            $success = 'ÄÄƒng kÃ½ thÃ nh cÃ´ng! Äang chuyá»ƒn hÆ°á»›ng...';
        } else {
            $error = 'ÄÄƒng kÃ½ tháº¥t báº¡i';
        }
    }
}

// Láº¥y mÃ£ giá»›i thiá»‡u chá»‰ tá»« URL (khÃ´ng tá»« Cookie)
$refCodeFromUrl = getRefCodeFromUrl();
$debugInfo = getDebugInfo();
?>

<main class="page-content">
    <section class="auth-section register-page">
        <div class="container">
            <h1 class="page-title-main">Account</h1>

            <div class="auth-panel register-panel">
                <h2 class="auth-heading">ÄÄƒng kÃ½</h2>
                <p class="auth-subheading">Tham gia ThuongLo.com Ä‘á»ƒ khÃ¡m phÃ¡ nguá»“n hÃ ng cháº¥t lÆ°á»£ng</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '<?php echo page_url('home'); ?>';
                        }, 2000);
                    </script>
                <?php endif; ?>

                <form method="POST" action="<?php echo form_url(); ?>" id="registerForm" class="auth-form">
                    <div class="form-group">
                        <label for="full_name">Há» vÃ  tÃªn <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               placeholder="Nháº­p há» vÃ  tÃªn Ä‘áº§y Ä‘á»§" required
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="Nháº­p Ä‘á»‹a chá»‰ email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Sá»‘ Ä‘iá»‡n thoáº¡i <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   placeholder="Nháº­p sá»‘ Ä‘iá»‡n thoáº¡i" required
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Máº­t kháº©u <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control"
                                       placeholder="Nháº­p máº­t kháº©u" required minlength="6">
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('password')"
                                        aria-label="Hiá»ƒn thá»‹ máº­t kháº©u" aria-pressed="false" data-label-show="Hiá»ƒn thá»‹ máº­t kháº©u"
                                        data-label-hide="áº¨n máº­t kháº©u">

                                    <span class="password-toggle-icon" id="password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">XÃ¡c nháº­n máº­t kháº©u <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                       placeholder="Nháº­p láº¡i máº­t kháº©u" required>
                                <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                        aria-label="Hiá»ƒn thá»‹ láº¡i máº­t kháº©u" aria-pressed="false" data-label-show="Hiá»ƒn thá»‹ láº¡i máº­t kháº©u"
                                        data-label-hide="áº¨n máº­t kháº©u">

                                    <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ref_code">MÃ£ giá»›i thiá»‡u</label>
                        <input type="text" id="ref_code" name="ref_code"
                               placeholder="Nháº­p mÃ£ giá»›i thiá»‡u (náº¿u cÃ³)"
                               value="<?php echo htmlspecialchars($refCodeFromUrl ?: ''); ?>"
                               class="form-control <?php echo $refCodeFromUrl ? 'readonly' : ''; ?>"
                               <?php echo $refCodeFromUrl ? 'readonly' : ''; ?>>

                        <?php if ($refCodeFromUrl): ?>
                            <div class="ref-code-info">
                                <span class="icon">âœ“</span>
                                MÃ£ giá»›i thiá»‡u Ä‘Ã£ Ä‘Æ°á»£c tá»± Ä‘á»™ng Ä‘iá»n tá»« link giá»›i thiá»‡u
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="terms-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            TÃ´i Ä‘á»“ng Ã½ vá»›i <a href="#" target="_blank">Äiá»u khoáº£n sá»­ dá»¥ng</a> vÃ 
                            <a href="#" target="_blank">ChÃ­nh sÃ¡ch báº£o máº­t</a> cá»§a ThuongLo.com
                        </label>
                    </div>

                    <button type="submit" class="btn-primary auth-submit-btn">Táº¡o tÃ i khoáº£n</button>
                </form>

                <div class="register-link">
                    ÄÃ£ cÃ³ tÃ i khoáº£n? <a href="<?php echo page_url('login'); ?>">ÄÄƒng nháº­p ngay</a>
                </div>
            </div>
        </div>
    </section>
</main>