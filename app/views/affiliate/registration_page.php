<?php
/**
 * Agent Registration Full Page View
 * Beautiful full-page registration form instead of popup
 */

// Start output buffering
ob_start();

$user = $user ?? [];
$csrf_token = $csrf_token ?? '';
$form_action = $form_action ?? '?page=agent&action=register';
$current_email = $current_email ?? ($user['email'] ?? '');
?>

<main class="page-content">
    <section class="agent-registration-section">
        <div class="container">
            <div class="registration-wrapper">

                <!-- Registration Form -->
                <div class="registration-form-section">
                    <h3 class="form-title">Thông tin đăng ký</h3>
                    
                    <?php 
                    // Get field-specific errors
                    $fieldErrors = $_SESSION['flash_errors'] ?? [];
                    if (!empty($fieldErrors)) {
                        unset($_SESSION['flash_errors']); // Clear after displaying
                    }
                    
                    // Get preserved form data
                    $formData = $_SESSION['agent_form_data'] ?? [];
                    if (!empty($formData)) {
                        unset($_SESSION['agent_form_data']); // Clear after using
                    }
                    ?>
                    
                    <script>
                    // Pass preserved data to JavaScript
                    window.agentFormData = <?php echo json_encode($formData); ?>;
                    </script>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($form_action); ?>" class="agent-registration-form">
                            <!-- CSRF Protection -->
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            
                            <div class="form-group">
                                <label for="agent_email" class="form-label">
                                    Email liên hệ <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                <?php 
                                // Multiple fallbacks for email
                                $emailValue = $current_email;
                                if (empty($emailValue)) {
                                    $emailValue = $user['email'] ?? '';
                                }
                                if (empty($emailValue)) {
                                    $emailValue = $_SESSION['user_email'] ?? '';
                                }
                                ?>
                                    <input type="email" 
                                           id="agent_email" 
                                           name="agent_email" 
                                           class="form-control readonly <?php echo isset($fieldErrors['agent_email']) ? 'error' : ''; ?>"
                                           placeholder=""
                                           value="<?php echo htmlspecialchars($emailValue); ?>"
                                           readonly
                                           tabindex="-1">
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" fill="none"/>
                                            <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" fill="none"/>
                                        </svg>
                                    </div>
                                </div>
                                <?php if (isset($fieldErrors['agent_email'])): ?>
                                    <div class="field-error"><?php echo htmlspecialchars($fieldErrors['agent_email']); ?></div>
                                <?php endif; ?>
                                <div class="form-help">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2"/>
                                        <path d="M12 17h.01" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    Email tài khoản của bạn sẽ được sử dụng để nhận thông báo
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name" class="form-label">
                                        Họ và tên đầy đủ <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" 
                                               id="full_name" 
                                               name="full_name" 
                                               class="form-control <?php echo isset($fieldErrors['full_name']) ? 'error' : ''; ?>"
                                               placeholder="Nhập họ và tên đầy đủ"
                                               value="<?php echo htmlspecialchars($formData['full_name'] ?? $user['name'] ?? ''); ?>"
                                               required>
                                        <div class="input-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" fill="none"/>
                                                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <?php if (isset($fieldErrors['full_name'])): ?>
                                        <div class="field-error"><?php echo htmlspecialchars($fieldErrors['full_name']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="phone_number" class="form-label">
                                        Số điện thoại <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="tel" 
                                               id="phone_number" 
                                               name="phone_number" 
                                               class="form-control <?php echo isset($fieldErrors['phone_number']) ? 'error' : ''; ?>"
                                               placeholder="Nhập số điện thoại"
                                               value="<?php echo htmlspecialchars($formData['phone_number'] ?? $user['phone'] ?? ''); ?>"
                                               required
                                               pattern="[0-9]{10,11}"
                                               title="Số điện thoại phải có 10-11 chữ số">
                                        <div class="input-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" fill="none"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <?php if (isset($fieldErrors['phone_number'])): ?>
                                        <div class="field-error"><?php echo htmlspecialchars($fieldErrors['phone_number']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="target_market" class="form-label">
                                    Thị trường mục tiêu
                                </label>
                                <?php 
                                $selectedMarkets = $formData['target_market'] ?? [];
                                if (!is_array($selectedMarkets)) {
                                    $selectedMarkets = [];
                                }
                                ?>
                                <div class="checkbox-group">
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="market_local" name="target_market[]" value="local" 
                                               <?php echo in_array('local', $selectedMarkets) ? 'checked' : ''; ?>>
                                        <label for="market_local">Thị trường địa phương</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="market_online" name="target_market[]" value="online"
                                               <?php echo in_array('online', $selectedMarkets) ? 'checked' : ''; ?>>
                                        <label for="market_online">Bán hàng online</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="market_social" name="target_market[]" value="social"
                                               <?php echo in_array('social', $selectedMarkets) ? 'checked' : ''; ?>>
                                        <label for="market_social">Mạng xã hội (Facebook, Instagram)</label>
                                    </div>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="market_b2b" name="target_market[]" value="b2b"
                                               <?php echo in_array('b2b', $selectedMarkets) ? 'checked' : ''; ?>>
                                        <label for="market_b2b">Bán buôn/B2B</label>
                                    </div>
                                </div>
                                <div class="form-help">
                                    Chọn các kênh bán hàng bạn dự định sử dụng (có thể chọn nhiều)
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="motivation" class="form-label">
                                    Lý do muốn trở thành đại lý
                                </label>
                                <textarea id="motivation" 
                                          name="motivation" 
                                          class="form-control"
                                          placeholder="Chia sẻ lý do bạn muốn trở thành đại lý của chúng tôi, kế hoạch kinh doanh, mục tiêu doanh thu..."
                                          rows="4"><?php echo htmlspecialchars($formData['motivation'] ?? ''); ?></textarea>
                                <div class="form-help">
                                    Thông tin này giúp chúng tôi hiểu rõ hơn về bạn và hỗ trợ tốt hơn
                                </div>
                            </div>

                            <div class="terms-section">
                                <div class="terms-checkbox">
                                    <input type="checkbox" id="agent_terms" name="agent_terms" required>
                                    <label for="agent_terms">
                                        Tôi đồng ý với <a href="#" target="_blank">Điều khoản đại lý</a> và 
                                        <a href="#" target="_blank">Chính sách hoa hồng</a> của ThuongLo.com
                                    </label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" fill="none"/>
                                        <polyline points="22,4 12,14.01 9,11.01" stroke="currentColor" stroke-width="2" fill="none"/>
                                    </svg>
                                    Gửi yêu cầu đăng ký
                                </button>
                                
                                <a href="?page=users&module=dashboard" class="btn-cancel">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18 6L6 18" stroke="currentColor" stroke-width="2"/>
                                        <path d="M6 6l12 12" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    Hủy bỏ
                                </a>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$content = ob_get_clean();

// Set page info for master layout
$page_title = 'Đăng ký trở thành đại lý';
$showBreadcrumb = true;
$breadcrumbs = [
    ['title' => 'Trang chủ', 'url' => './'],
    ['title' => 'Đăng ký đại lý']
];

// Include master layout (standard website layout with header/footer)
include __DIR__ . '/../_layout/master.php';