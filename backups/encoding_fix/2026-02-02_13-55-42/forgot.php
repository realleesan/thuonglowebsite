<?php
require_once __DIR__ . '/auth.php'; 

if (!function_exists('forgot_redirect')) {
    function forgot_redirect(string $url): void {
        if (!headers_sent()) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Location: ' . $url);
            exit;
        }

        echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        exit;
    }
}

// --- 0. LOGIC RESET (Má»šI THÃŠM) ---
// Náº¿u ngÆ°á»i dÃ¹ng báº¥m tá»« trang Login sang (cÃ³ ?reset=true)
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    // XÃ³a sáº¡ch session liÃªn quan Ä‘áº¿n quy trÃ¬nh quÃªn máº­t kháº©u
    unset($_SESSION['forgot_step'], $_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['flash_error'], $_SESSION['flash_success']);
    
    // Chuyá»ƒn hÆ°á»›ng láº¡i chÃ­nh trang nÃ y (bá» ?reset=true) Ä‘á»ƒ báº¯t Ä‘áº§u sáº¡ch sáº½
    forgot_redirect(page_url('forgot'));
}

// --- 1. Láº¤Y Dá»® LIá»†U Tá»ª SESSION (QUAN TRá»ŒNG) ---
$step = $_SESSION['forgot_step'] ?? 'input'; 
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';

// XÃ³a thÃ´ng bÃ¡o sau khi Ä‘Ã£ láº¥y ra (Ä‘á»ƒ F5 khÃ´ng hiá»‡n láº¡i)F
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// --- HÃ€M GIáº¢ Láº¬P (Giá»¯ nguyÃªn) ---
if (!function_exists('sendResetEmail')) {
    function sendResetEmail($email, $code) { return true; }
}
if (!function_exists('sendResetSMS')) {
    function sendResetSMS($phone, $code) { return true; }
}

// --- 2. Xá»¬ LÃ POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_contact') {
        // Reset vá» bÆ°á»›c 1
        unset($_SESSION['reset_code'], $_SESSION['reset_contact'], $_SESSION['reset_expires']);
        $_SESSION['forgot_step'] = 'input'; // LÆ°u vÃ o session
        $_SESSION['flash_success'] = 'Má»i nháº­p thÃ´ng tin liÃªn há»‡ má»›i.';
    } 
    elseif ($action === 'send_code') {
        $contact = sanitize($_POST['contact'] ?? '');
        if (empty($contact)) {
            $_SESSION['flash_error'] = 'Vui lÃ²ng nháº­p thÃ´ng tin.';
        } else {
            // Giáº£ láº­p gá»­i mÃ£
            $code = rand(100000, 999999);
            $_SESSION['reset_code'] = $code;
            $_SESSION['reset_contact'] = $contact;
            $_SESSION['reset_expires'] = time() + 600;
            
            // Chuyá»ƒn sang bÆ°á»›c 2 (LÆ°u vÃ o session)
            $_SESSION['forgot_step'] = 'verify';
            $_SESSION['flash_success'] = "Gá»­i thÃ nh cÃ´ng! MÃ£ test cá»§a báº¡n lÃ : <b>$code</b>";
        }
    } 
    elseif ($action === 'verify_code') {
        $inputCode = sanitize($_POST['verification_code'] ?? '');
        $savedCode = $_SESSION['reset_code'] ?? '';
        
        // Kiá»ƒm tra mÃ£ (LÆ°u Ã½: dÃ¹ng != thay vÃ¬ !== Ä‘á»ƒ so sÃ¡nh lá»ng giá»¯a chuá»—i vÃ  sá»‘)
        if (empty($savedCode) || $inputCode != $savedCode) {
            $_SESSION['flash_error'] = 'MÃ£ xÃ¡c thá»±c sai hoáº·c Ä‘Ã£ háº¿t háº¡n!';
            // Giá»¯ nguyÃªn step lÃ  verify
        } else {
            // Chuyá»ƒn sang bÆ°á»›c 3 (LÆ°u vÃ o session)
            $_SESSION['forgot_step'] = 'reset';
            $_SESSION['flash_success'] = 'MÃ£ Ä‘Ãºng! Má»i Ä‘áº·t máº­t kháº©u má»›i.';
        }
    } 
    elseif ($action === 'reset_password') {
        $p1 = $_POST['new_password'] ?? '';
        $p2 = $_POST['confirm_password'] ?? '';
        
        if (strlen($p1) < 6) {
             $_SESSION['flash_error'] = 'Máº­t kháº©u pháº£i tá»« 6 kÃ½ tá»± trá»Ÿ lÃªn!';
        } elseif ($p1 !== $p2) {
             $_SESSION['flash_error'] = 'Máº­t kháº©u khÃ´ng khá»›p!';
        } else {
            // ThÃ nh cÃ´ng -> XÃ³a session rÃ¡c
            unset($_SESSION['reset_code'], $_SESSION['forgot_step'], $_SESSION['reset_contact']);
            $_SESSION['flash_success'] = 'Äá»•i máº­t kháº©u thÃ nh cÃ´ng! Vui lÃ²ng Ä‘Äƒng nháº­p láº¡i.';
            
            // LÆ°u session vÃ  Chuyá»ƒn hÆ°á»›ng vá» trang Login
            session_write_close(); 
            forgot_redirect(page_url('login'));
        }
    }
    
    // --- CHUYá»‚N HÆ¯á»šNG Vá»€ CHÃNH TRANG NÃ€Y Äá»‚ TRÃNH Lá»–I FORM RESUBMISSION ---
    session_write_close(); // Äáº£m báº£o Session Ä‘Æ°á»£c lÆ°u trÆ°á»›c khi chuyá»ƒn trang
    forgot_redirect(page_url('forgot'));
}
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container forgot-wrapper">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-forgot">

                <section class="forgot-form-section">
                    <div class="container">
                        <div class="auth-panel forgot-panel">
                            <h2 class="auth-heading">KhÃ´i phá»¥c tÃ i khoáº£n</h2>

                            <div class="step-indicator">
                                <div class="step <?php echo $step === 'input' ? 'active' : 'completed'; ?>">
                                    <span>1</span>
                                    <p>Nháº­p thÃ´ng tin</p>
                                </div>
                                <div class="step-line"></div>
                                <div class="step <?php echo $step === 'verify' ? 'active' : ($step === 'reset' ? 'completed' : ''); ?>">
                                    <span>2</span>
                                    <p>Nháº­p mÃ£</p>
                                </div>
                                <div class="step-line"></div>
                                <div class="step <?php echo $step === 'reset' ? 'active' : ''; ?>">
                                    <span>3</span>
                                    <p>Äá»•i máº­t kháº©u</p>
                                </div>
                            </div>

                            <?php if ($error): ?>
                                <div class="alert alert-error"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <?php if ($step === 'input'): ?>
                                <form method="POST" action="<?php echo form_url('forgot'); ?>" class="auth-form">
                                    <input type="hidden" name="action" value="send_code">
                                    <div class="form-group">
                                        <label for="contact">Email hoáº·c Sá»‘ Ä‘iá»‡n thoáº¡i</label>
                                        <input type="text" id="contact" name="contact" class="form-control" placeholder="Nháº­p Email hoáº·c SÄT" required autofocus>
                                    </div>
                                    <button type="submit" class="btn-primary auth-submit-btn">Gá»­i mÃ£ xÃ¡c thá»±c</button>
                                </form>

                                <div class="forgot-info">
                                    <p>Há»‡ thá»‘ng sáº½ gá»­i mÃ£ code 6 chá»¯ sá»‘ tá»›i thÃ´ng tin báº¡n cung cáº¥p. MÃ£ cÃ³ hiá»‡u lá»±c trong 10 phÃºt.</p>
                                </div>

                            <?php elseif ($step === 'verify'): ?>
                                <form method="POST" action="<?php echo form_url('forgot'); ?>" class="auth-form">
                                    <div class="form-group">
                                        <label for="verification_code">Nháº­p mÃ£ xÃ¡c thá»±c</label>
                                        <input type="text" id="verification_code" name="verification_code" class="form-control code-input"
                                               placeholder="000000" maxlength="6" required autofocus>
                                    </div>
                                    <button type="submit" class="btn-primary auth-submit-btn" name="action" value="verify_code">XÃ¡c thá»±c</button>

                                    <div class="forgot-actions">
                                        <button type="submit" name="action" value="change_contact" class="ref-code-action" formnovalidate>
                                            Nháº­p láº¡i thÃ´ng tin khÃ¡c
                                        </button>
                                    </div>
                                </form>

                            <?php elseif ($step === 'reset'): ?>
                                <form method="POST" action="<?php echo form_url('forgot'); ?>" class="auth-form">
                                    <input type="hidden" name="action" value="reset_password">

                                    <div class="form-group">
                                        <label class="form-label" for="new_password">Máº­t kháº©u má»›i</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Máº­t kháº©u má»›i" required autofocus>
                                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('new_password')"
                                                    aria-label="Hiá»ƒn thá»‹ máº­t kháº©u má»›i" aria-pressed="false" data-label-show="Hiá»ƒn thá»‹ máº­t kháº©u má»›i"
                                                    data-label-hide="áº¨n máº­t kháº©u má»›i">
                                                <span class="password-toggle-icon" id="new-password-icon" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="confirm_password">Nháº­p láº¡i máº­t kháº©u</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nháº­p láº¡i máº­t kháº©u" required>
                                            <button type="button" class="password-toggle" onclick="toggleAuthPassword('confirm_password')"
                                                    aria-label="Hiá»ƒn thá»‹ láº¡i máº­t kháº©u" aria-pressed="false" data-label-show="Hiá»ƒn thá»‹ láº¡i máº­t kháº©u"
                                                    data-label-hide="áº¨n máº­t kháº©u">
                                                <span class="password-toggle-icon" id="confirm-password-icon" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-primary auth-submit-btn">Äá»•i máº­t kháº©u</button>
                                </form>
                            <?php endif; ?>

                            <div class="register-link">
                                <a href="<?php echo page_url('login'); ?>">â† Quay láº¡i Ä‘Äƒng nháº­p</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>