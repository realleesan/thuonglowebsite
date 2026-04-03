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
                    
                    // Check if user has pending request - show processing message instead
                    $pendingStatus = $_SESSION['agent_request_status'] ?? 'none';
                    if ($pendingStatus === 'pending') {
                        // User has pending request - redirect to processing page
                        header('Location: ?page=agent&action=processing');
                        exit;
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
                                               value="<?php echo htmlspecialchars($formData['phone_number'] ?? $current_phone ?? $user['phone'] ?? ''); ?>"
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
                                        Tôi đồng ý với <a href="javascript:void(0)" class="terms-link" onclick="openModal('agentTermsModal')">Điều khoản đại lý</a> và <a href="javascript:void(0)" class="terms-link" onclick="openModal('commissionModal')">Chính sách hoa hồng</a> của ThuongLo.com
                                    </label>
                                </div>
                            </div>

                            <!-- Agent Terms Modal -->
                            <div id="agentTermsModal" class="modal-overlay">
                                <div class="modal-content">
                                    <button class="modal-close" onclick="closeModal('agentTermsModal')">&times;</button>
                                    <h3>Điều khoản đại lý</h3>
                                    <div class="modal-body">
                                        <h4>1. Quyền và nghĩa vụ của đại lý</h4>
                                        <p>Đại lý được quyền sử dụng mã giới thiệu cá nhân để giới thiệu khách hàng. Đại lý có nghĩa vụ tuân thủ quy định marketing và không được phép spama hoặc làm giả thông tin.</p>
                                        <h4>2. Quyền lợi hoa hồng</h4>
                                        <p>Đại lý nhận hoa hồng từ mọi đơn hàng của khách giới thiệu. Hoa hồng được tính trọn đời cho khách hàng giới thiệu.</p>
                                        <h4>3. Thanh toán</h4>
                                        <p>Hoa hồng thanh toán vào ngày 15 hàng tháng. Số tiền tối thiểu để thanh toán là 100.000 VNĐ.</p>
                                        <h4>4. Chấm dứt hợp đồng</h4>
                                        <p>ThuongLo.com có quyền chấm dứt quan hệ đại lý nếu đại lý vi phạm điều khoản hoặc không đạt doanh số tối thiểu trong 3 tháng liên tiếp.</p>
                                        <h4>5. Bảo mật thông tin</h4>
                                        <p>Đại lý cam kết bảo mật thông tin khách hàng và không được phép tiết lộ cho bên thứ ba.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Commission Modal -->
                            <div id="commissionModal" class="modal-overlay">
                                <div class="modal-content">
                                    <button class="modal-close" onclick="closeModal('commissionModal')">&times;</button>
                                    <h3>Chính sách hoa hồng</h3>
                                    <div class="modal-body">
                                        <h4>Hoa hồng trọn đời</h4>
                                        <p>Bạn nhận hoa hồng từ TẤT CẢ đơn hàng của khách giới thiệu trọn đời.</p>
                                        <h4>Các loại hoa hồng</h4>
                                        <p>- Đăng ký gói dữ liệu: 10%</p>
                                        <p>- Vận chuyển: 5%</p>
                                        <h4>Mức hoa hồng theo cấp</h4>
                                        <p>- Đồng (Bronze): 10%</p>
                                        <p>- Bạc (Silver): 12%</p>
                                        <p>- Vàng (Gold): 15%</p>
                                        <p>- Kim cương (Diamond): 20%</p>
                                        <h4>Thanh toán</h4>
                                        <p>Kỳ thanh toán: Hàng tháng (ngày 15)</p>
                                        <p>Số tiền tối thiểu: 100.000 VNĐ</p>
                                    </div>
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

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        setTimeout(() => {
            e.target.style.display = 'none';
        }, 300);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });
    }
});
</script>

<style>
/* Terms link styling - bold blue text */
.terms-link {
    color: #007bff;
    font-weight: 700;
    text-decoration: none;
}

.terms-link:hover {
    color: #333333 !important;
    text-decoration: none !important;
}

/* Modal Overlay */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    display: flex;
    opacity: 1;
}

/* Modal Content */
.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 550px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
    transform: scale(0.8);
    transition: transform 0.3s ease;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
}

.modal-overlay.active .modal-content {
    transform: scale(1);
}

/* Modal Close Button */
.modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 32px;
    height: 32px;
    border: none;
    background: #f0f0f0;
    border-radius: 50%;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: #e0e0e0;
    color: #333;
}

/* Modal Header */
.modal-content h3 {
    padding: 20px 24px 0 24px;
    margin: 0;
    color: #333;
    font-size: 20px;
    font-weight: 700;
}

/* Modal Body */
.modal-body {
    padding: 16px 24px 24px 24px;
}

.modal-body h4 {
    margin: 16px 0 8px 0;
    color: #333;
    font-size: 15px;
    font-weight: 600;
}

.modal-body h4:first-child {
    margin-top: 0;
}

.modal-body p {
    margin: 0 0 8px 0;
    line-height: 1.6;
    color: #555;
    font-size: 14px;
}

/* Scrollbar styling */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}
</style>

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