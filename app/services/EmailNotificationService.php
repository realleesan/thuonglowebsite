<?php
/**
 * EmailNotificationService - Service gửi email thông báo
 * Triển khai service gửi email sử dụng PHPMailer hiện có
 * Requirements: 1.4, 2.3, 3.4, 5.1, 5.2, 5.3, 5.4
 */

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/../../assets/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../assets/vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../assets/vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailNotificationService implements ServiceInterface {
    private PHPMailer $mailer;
    private array $emailConfig;
    private array $templates;
    
    public function __construct() {
        $this->initializeMailer();
        $this->loadEmailConfig();
        $this->loadEmailTemplates();
    }
    
    /**
     * ServiceInterface implementation
     */
    public function getData(string $method, array $params = []): array {
        try {
            switch ($method) {
                case 'sendRegistrationConfirmation':
                    return [
                        'success' => $this->sendRegistrationConfirmation(
                            $params['email'] ?? '', 
                            $params['name'] ?? ''
                        )
                    ];
                case 'sendApprovalNotification':
                    return [
                        'success' => $this->sendApprovalNotification(
                            $params['email'] ?? '', 
                            $params['name'] ?? ''
                        )
                    ];
                case 'sendProcessingNotification':
                    return [
                        'success' => $this->sendProcessingNotification(
                            $params['email'] ?? '', 
                            $params['name'] ?? ''
                        )
                    ];
                default:
                    throw new Exception("Unknown method: $method");
            }
        } catch (Exception $e) {
            return $this->handleError($e, ['method' => $method, 'params' => $params]);
        }
    }
    
    public function getModel(string $modelName) {
        return null; // Email service doesn't use models
    }
    
    public function handleError(\Exception $e, array $context = []): array {
        error_log("EmailNotificationService Error: " . $e->getMessage() . " Context: " . json_encode($context));
        return [
            'success' => false,
            'error' => true,
            'message' => 'Có lỗi xảy ra khi gửi email thông báo'
        ];
    }
    
    /**
     * Gửi email xác nhận đăng ký đại lý
     * Requirements: 1.4, 2.3, 5.1
     */
    public function sendRegistrationConfirmation(string $userEmail, string $userName): bool {
        try {
            $this->setupMailer();
            
            // Recipient
            $this->mailer->addAddress($userEmail, $userName);
            
            // Content
            $this->mailer->Subject = 'Xác nhận đăng ký làm đại lý - ThuongLo';
            
            $emailBody = $this->getEmailTemplate('registration_confirmation', [
                'user_name' => $userName,
                'processing_time' => '24 giờ',
                'contact_email' => $this->emailConfig['support_email'],
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gửi email thông báo phê duyệt thành công
     * Requirements: 3.4, 5.2
     */
    public function sendApprovalNotification(string $userEmail, string $userName): bool {
        try {
            $this->setupMailer();
            
            // Recipient
            $this->mailer->addAddress($userEmail, $userName);
            
            // Content
            $this->mailer->Subject = 'Chúc mừng! Bạn đã trở thành đại lý ThuongLo';
            
            $emailBody = $this->getEmailTemplate('approval_notification', [
                'user_name' => $userName,
                'login_url' => $this->getBaseUrl() . '/auth/login',
                'dashboard_url' => $this->getBaseUrl() . '/affiliate/dashboard',
                'contact_email' => $this->emailConfig['support_email'],
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gửi email thông báo đang xử lý
     * Requirements: 5.3
     */
    public function sendProcessingNotification(string $userEmail, string $userName): bool {
        try {
            $this->setupMailer();
            
            // Recipient
            $this->mailer->addAddress($userEmail, $userName);
            
            // Content
            $this->mailer->Subject = 'Đang xử lý yêu cầu đăng ký đại lý - ThuongLo';
            
            $emailBody = $this->getEmailTemplate('processing_notification', [
                'user_name' => $userName,
                'processing_time' => '24 giờ',
                'status_check_url' => $this->getBaseUrl() . '/user/dashboard',
                'contact_email' => $this->emailConfig['support_email'],
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Khởi tạo PHPMailer
     */
    private function initializeMailer(): void {
        $this->mailer = new PHPMailer(true);
    }
    
    /**
     * Load cấu hình email
     */
    private function loadEmailConfig(): void {
        // Default email configuration
        $this->emailConfig = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => 'your-email@gmail.com', // Cần cấu hình
            'smtp_password' => 'your-app-password', // Cần cấu hình
            'from_email' => 'noreply@thuonglo.com',
            'from_name' => 'ThuongLo',
            'support_email' => 'support@thuonglo.com',
            'use_smtp' => true,
            'smtp_auth' => true,
            'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS,
            'charset' => 'UTF-8'
        ];
        
        // Override with environment-specific config if exists
        if (file_exists(__DIR__ . '/../../config/email.php')) {
            $envConfig = include __DIR__ . '/../../config/email.php';
            $this->emailConfig = array_merge($this->emailConfig, $envConfig);
        }
    }
    
    /**
     * Setup PHPMailer với cấu hình
     */
    private function setupMailer(): void {
        // Server settings
        if ($this->emailConfig['use_smtp']) {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->emailConfig['smtp_host'];
            $this->mailer->SMTPAuth = $this->emailConfig['smtp_auth'];
            $this->mailer->Username = $this->emailConfig['smtp_username'];
            $this->mailer->Password = $this->emailConfig['smtp_password'];
            
            // Handle smtp_secure setting
            $smtpSecure = $this->emailConfig['smtp_secure'];
            if ($smtpSecure === 'tls') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($smtpSecure === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = $smtpSecure; // Use as-is if already a constant
            }
            
            $this->mailer->Port = $this->emailConfig['smtp_port'];
        }
        
        // Recipients
        $this->mailer->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
        $this->mailer->addReplyTo($this->emailConfig['support_email'], $this->emailConfig['from_name']);
        
        // Content
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = $this->emailConfig['charset'];
    }
    
    /**
     * Reset PHPMailer để sử dụng lại
     */
    private function resetMailer(): void {
        $this->mailer->clearAddresses();
        $this->mailer->clearAttachments();
        $this->mailer->clearReplyTos();
        $this->mailer->clearAllRecipients();
        $this->mailer->clearCustomHeaders();
    }
    
    /**
     * Load email templates
     */
    private function loadEmailTemplates(): void {
        $this->templates = [
            'registration_confirmation' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #2c3e50;">Xin chào {{user_name}}!</h2>
                    <p>Cảm ơn bạn đã đăng ký làm đại lý tại <strong>{{website_name}}</strong>.</p>
                    <p>Chúng tôi đã nhận được yêu cầu đăng ký của bạn và sẽ xử lý trong vòng <strong>{{processing_time}}</strong>.</p>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h3 style="color: #495057; margin-top: 0;">Thông tin quan trọng:</h3>
                        <ul style="color: #6c757d;">
                            <li>Thời gian xử lý: {{processing_time}}</li>
                            <li>Bạn sẽ nhận được email thông báo kết quả</li>
                            <li>Trong thời gian chờ, bạn có thể sử dụng tài khoản với tư cách người dùng thông thường</li>
                        </ul>
                    </div>
                    <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ: <a href="mailto:{{contact_email}}">{{contact_email}}</a></p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'approval_notification' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #28a745;">🎉 Chúc mừng {{user_name}}!</h2>
                    <p>Yêu cầu đăng ký làm đại lý của bạn đã được <strong style="color: #28a745;">PHÊ DUYỆT</strong>!</p>
                    <p>Bạn đã chính thức trở thành đại lý của <strong>{{website_name}}</strong>.</p>
                    
                    <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
                        <h3 style="color: #155724; margin-top: 0;">Bước tiếp theo:</h3>
                        <ol style="color: #155724;">
                            <li><a href="{{login_url}}" style="color: #007bff;">Đăng nhập vào tài khoản</a></li>
                            <li><a href="{{dashboard_url}}" style="color: #007bff;">Truy cập bảng điều khiển đại lý</a></li>
                            <li>Bắt đầu kinh doanh và kiếm hoa hồng</li>
                        </ol>
                    </div>
                    
                    <p>Chúc bạn thành công trong vai trò đại lý mới!</p>
                    <p>Nếu cần hỗ trợ, liên hệ: <a href="mailto:{{contact_email}}">{{contact_email}}</a></p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'processing_notification' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #ffc107;">&#9203; Đang xử lý yêu cầu</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Chúng tôi đang xử lý yêu cầu đăng ký làm đại lý của bạn.</p>
                    
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                        <h3 style="color: #856404; margin-top: 0;">Thời gian xử lý:</h3>
                        <p style="color: #856404; margin-bottom: 0;">Dự kiến trong vòng <strong>{{processing_time}}</strong></p>
                    </div>
                    
                    <p>Bạn có thể <a href="{{status_check_url}}" style="color: #007bff;">kiểm tra trạng thái</a> trong tài khoản của mình.</p>
                    <p>Chúng tôi sẽ gửi email thông báo ngay khi có kết quả.</p>
                    
                    <p>Cảm ơn sự kiên nhẫn của bạn!</p>
                    <p>Liên hệ hỗ trợ: <a href="mailto:{{contact_email}}">{{contact_email}}</a></p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
<<<<<<< Updated upstream

            'device_verification' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
                        <h2 style="color: #ffffff; margin: 0; font-size: 24px;">Xác thực đăng nhập</h2>
                        <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0;">{{website_name}}</p>
                    </div>
                    
                    <div style="padding: 30px; border: 1px solid #e9ecef; border-top: none; border-radius: 0 0 8px 8px;">
                        <p style="font-size: 16px;">Xin chào <strong>{{user_name}}</strong>,</p>
                        <p>Chúng tôi nhận được yêu cầu đăng nhập từ một thiết bị mới. Sử dụng mã bên dưới để xác thực:</p>
                        
                        <div style="background-color: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0; text-align: center; border: 2px dashed #667eea;">
                            <p style="margin: 0 0 8px 0; color: #6c757d; font-size: 14px;">Mã xác thực của bạn</p>
                            <p style="font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 8px; margin: 0;">{{verification_code}}</p>
                            <p style="margin: 8px 0 0 0; color: #dc3545; font-size: 13px;">Mã có hiệu lực trong {{expiry_minutes}} phút</p>
                        </div>
                        
                        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                            <h4 style="color: #856404; margin: 0 0 10px 0;">Thông tin thiết bị yêu cầu:</h4>
                            <table style="width: 100%; color: #856404; font-size: 14px;">
                                <tr><td style="padding: 3px 0;"><strong>Thiết bị:</strong></td><td>{{device_name}}</td></tr>
                                <tr><td style="padding: 3px 0;"><strong>Trình duyệt:</strong></td><td>{{browser}}</td></tr>
                                <tr><td style="padding: 3px 0;"><strong>Hệ điều hành:</strong></td><td>{{os}}</td></tr>
                                <tr><td style="padding: 3px 0;"><strong>Địa chỉ IP:</strong></td><td>{{ip_address}}</td></tr>
                                <tr><td style="padding: 3px 0;"><strong>Vị trí:</strong></td><td>{{location}}</td></tr>
                            </table>
                        </div>
                        
                        <div style="background-color: #f8d7da; padding: 12px 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545;">
                            <p style="color: #721c24; margin: 0; font-size: 13px;"><strong>Lưu ý bảo mật:</strong> Nếu bạn không yêu cầu đăng nhập này, hãy bỏ qua email này và đổi mật khẩu ngay lập tức.</p>
                        </div>
                        
                        <p style="color: #6c757d; font-size: 14px; margin-top: 25px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                    </div>
=======
            
            'order_confirmation' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #2c3e50;">Xác nhận đơn hàng #{{order_number}}</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Cảm ơn bạn đã đặt hàng tại <strong>{{website_name}}</strong>!</p>
                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h3 style="color: #495057; margin-top: 0;">Thông tin đơn hàng:</h3>
                        <ul style="color: #6c757d;">
                            <li>Mã đơn hàng: <strong>{{order_number}}</strong></li>
                            <li>Ngày đặt: {{order_date}}</li>
                            <li>Tổng tiền: <strong>{{total}} VND</strong></li>
                        </ul>
                    </div>
                    <p>Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'payment_success' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #28a745;">✅ Thanh toán thành công!</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Đơn hàng <strong>#{{order_number}}</strong> của bạn đã được thanh toán thành công.</p>
                    <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
                        <h3 style="color: #155724; margin-top: 0;">Chi tiết thanh toán:</h3>
                        <ul style="color: #155724;">
                            <li>Số tiền: <strong>{{amount}} VND</strong></li>
                            <li>Phương thức: {{payment_method}}</li>
                            <li>Thời gian: {{payment_date}}</li>
                        </ul>
                    </div>
                    <p>Đơn hàng của bạn đang được xử lý. Cảm ơn bạn đã mua hàng!</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'payment_failed' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #dc3545;">❌ Thanh toán thất bại</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Rất tiếc, thanh toán cho đơn hàng <strong>#{{order_number}}</strong> đã thất bại.</p>
                    <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                        <h3 style="color: #721c24; margin-top: 0;">Lý do:</h3>
                        <p style="color: #721c24; margin-bottom: 0;">{{reason}}</p>
                    </div>
                    <p>Vui lòng thử lại hoặc liên hệ với chúng tôi để được hỗ trợ.</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'commission_earned' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #28a745;">💰 Bạn nhận được hoa hồng mới!</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Chúc mừng! Bạn vừa nhận được hoa hồng từ đơn hàng <strong>#{{order_number}}</strong></p>
                    <div style="background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center; border-left: 4px solid #28a745;">
                        <h3 style="color: #155724; margin-top: 0;">Số tiền hoa hồng:</h3>
                        <p style="font-size: 32px; color: #28a745; font-weight: bold; margin: 10px 0;">{{commission}} VND</p>
                        <p style="color: #155724; margin-bottom: 0;">Thời gian: {{date}}</p>
                    </div>
                    <p>Hoa hồng đã được cộng vào ví của bạn và có thể rút về tài khoản ngân hàng.</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'withdrawal_request_admin' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #ffc107;">🔔 Yêu cầu rút tiền mới</h2>
                    <p>Có yêu cầu rút tiền mới từ đại lý cần xử lý.</p>
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                        <h3 style="color: #856404; margin-top: 0;">Thông tin rút tiền:</h3>
                        <ul style="color: #856404;">
                            <li>Mã rút tiền: <strong>{{withdraw_code}}</strong></li>
                            <li>Đại lý: {{affiliate_name}}</li>
                            <li>Số tiền: <strong>{{amount}} VND</strong></li>
                            <li>Ngân hàng: {{bank_name}}</li>
                            <li>Số tài khoản: {{account_number}}</li>
                            <li>Chủ tài khoản: {{account_holder}}</li>
                            <li>Thời gian yêu cầu: {{requested_at}}</li>
                        </ul>
                    </div>
                    <p>Vui lòng đăng nhập vào hệ thống để xử lý yêu cầu này.</p>
                    <p style="color: #6c757d; font-size: 14px;">Hệ thống {{website_name}}</p>
                </div>
            ',
            
            'withdrawal_completed' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #28a745;">✅ Rút tiền thành công!</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Yêu cầu rút tiền <strong>{{withdraw_code}}</strong> của bạn đã được xử lý thành công.</p>
                    <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
                        <h3 style="color: #155724; margin-top: 0;">Chi tiết rút tiền:</h3>
                        <ul style="color: #155724;">
                            <li>Số tiền: <strong>{{amount}} VND</strong></li>
                            <li>Ngân hàng: {{bank_name}}</li>
                            <li>Số tài khoản: {{account_number}}</li>
                            <li>Thời gian hoàn tất: {{completed_at}}</li>
                        </ul>
                    </div>
                    <p>Tiền đã được chuyển vào tài khoản ngân hàng của bạn. Vui lòng kiểm tra.</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'withdrawal_rejected' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #dc3545;">❌ Yêu cầu rút tiền bị từ chối</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Rất tiếc, yêu cầu rút tiền <strong>{{withdraw_code}}</strong> của bạn đã bị từ chối.</p>
                    <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                        <h3 style="color: #721c24; margin-top: 0;">Lý do từ chối:</h3>
                        <p style="color: #721c24; margin-bottom: 0;">{{reason}}</p>
                    </div>
                    <p>Số tiền <strong>{{amount}} VND</strong> đã được hoàn lại vào ví của bạn.</p>
                    <p>Nếu có thắc mắc, vui lòng liên hệ với chúng tôi.</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
                </div>
            ',
            
            'otp_verification' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #007bff;">🔐 Mã xác thực OTP</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Bạn đang thực hiện thay đổi thông tin ngân hàng. Vui lòng sử dụng mã OTP dưới đây để xác thực:</p>
                    <div style="background-color: #cfe2ff; padding: 30px; border-radius: 5px; margin: 20px 0; text-align: center; border-left: 4px solid #007bff;">
                        <p style="color: #084298; margin: 0 0 10px 0;">Mã OTP của bạn:</p>
                        <p style="font-size: 48px; color: #007bff; font-weight: bold; letter-spacing: 8px; margin: 10px 0;">{{otp}}</p>
                        <p style="color: #084298; margin: 10px 0 0 0;">Mã có hiệu lực trong {{expires_in}}</p>
                    </div>
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <p style="color: #856404; margin: 0;"><strong>⚠️ Lưu ý:</strong> Không chia sẻ mã này với bất kỳ ai, kể cả nhân viên {{website_name}}!</p>
                    </div>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
>>>>>>> Stashed changes
                </div>
            '
        ];
    }
    
    /**
     * Lấy email template với placeholder replacement
     */
    private function getEmailTemplate(string $templateName, array $variables = []): string {
        if (!isset($this->templates[$templateName])) {
            return 'Email template not found';
        }
        
        $template = $this->templates[$templateName];
        
        // Replace placeholders
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * Lấy base URL của website
     */
    private function getBaseUrl(): string {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    /**
     * Gửi email mã xác thực thiết bị (OTP 6 số)
     */
    public function sendDeviceVerificationCode(string $userEmail, string $userName, string $code, array $deviceInfo = []): bool {
        try {
            $this->setupMailer();
            
            // Recipient
            $this->mailer->addAddress($userEmail, $userName);
            
            // Content
            $this->mailer->Subject = 'Mã xác thực đăng nhập - ThuongLo';
            
            $emailBody = $this->getEmailTemplate('device_verification', [
                'user_name' => $userName,
                'verification_code' => $code,
                'device_name' => $deviceInfo['device_name'] ?? 'Thiết bị không xác định',
                'ip_address' => $deviceInfo['ip_address'] ?? 'N/A',
                'location' => $deviceInfo['location'] ?? 'N/A',
                'browser' => $deviceInfo['browser'] ?? 'N/A',
                'os' => $deviceInfo['os'] ?? 'N/A',
                'expiry_minutes' => '5',
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = "Mã xác thực của bạn là: {$code}. Mã có hiệu lực trong 5 phút.";
            
            $result = $this->mailer->send();
            $this->resetMailer();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Device verification email failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test email configuration
     */
    public function testEmailConfiguration(): array {
        try {
            $this->setupMailer();
            
            // Test SMTP connection
            if ($this->emailConfig['use_smtp']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
                $this->mailer->Debugoutput = function($str, $level) {
                    return $str; // Capture debug output
                };
            }
            
            return [
                'success' => true,
                'message' => 'Email configuration is valid',
                'config' => [
                    'smtp_host' => $this->emailConfig['smtp_host'],
                    'smtp_port' => $this->emailConfig['smtp_port'],
                    'from_email' => $this->emailConfig['from_email'],
                    'use_smtp' => $this->emailConfig['use_smtp']
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Email configuration error: ' . $e->getMessage()
            ];
        }
    }
    
    // ==================== PAYMENT & WALLET NOTIFICATIONS ====================
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(string $email, string $name, array $orderData): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Xác nhận đơn hàng #{$orderData['order_number']}";
            
            $emailBody = $this->getEmailTemplate('order_confirmation', [
                'user_name' => $name,
                'order_number' => $orderData['order_number'],
                'order_date' => $orderData['created_at'] ?? date('Y-m-d H:i:s'),
                'total' => number_format($orderData['total'] ?? 0, 0, ',', '.'),
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send payment success email
     */
    public function sendPaymentSuccess(string $email, string $name, array $orderData): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Thanh toán thành công - Đơn hàng #{$orderData['order_number']}";
            
            $emailBody = $this->getEmailTemplate('payment_success', [
                'user_name' => $name,
                'order_number' => $orderData['order_number'],
                'amount' => number_format($orderData['total'] ?? 0, 0, ',', '.'),
                'payment_method' => $orderData['payment_method'] ?? 'SePay',
                'payment_date' => date('d/m/Y H:i'),
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send payment failed email
     */
    public function sendPaymentFailed(string $email, string $name, array $orderData, string $reason): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Thanh toán thất bại - Đơn hàng #{$orderData['order_number']}";
            
            $emailBody = $this->getEmailTemplate('payment_failed', [
                'user_name' => $name,
                'order_number' => $orderData['order_number'],
                'amount' => number_format($orderData['total'] ?? 0, 0, ',', '.'),
                'reason' => $reason,
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send commission earned notification
     */
    public function sendCommissionEarned(string $email, string $name, float $commission, string $orderNumber): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Bạn nhận được hoa hồng mới!";
            
            $emailBody = $this->getEmailTemplate('commission_earned', [
                'user_name' => $name,
                'commission' => number_format($commission, 0, ',', '.'),
                'order_number' => $orderNumber,
                'date' => date('d/m/Y H:i'),
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send withdrawal request notification (to admin)
     */
    public function sendWithdrawalRequestToAdmin(array $withdrawalData): bool {
        try {
            $this->setupMailer();
            $adminEmail = $this->emailConfig['support_email'];
            $this->mailer->addAddress($adminEmail, 'Admin');
            $this->mailer->Subject = "Yêu cầu rút tiền mới - {$withdrawalData['withdraw_code']}";
            
            $emailBody = $this->getEmailTemplate('withdrawal_request_admin', [
                'withdraw_code' => $withdrawalData['withdraw_code'],
                'affiliate_name' => $withdrawalData['affiliate_name'] ?? 'N/A',
                'amount' => number_format($withdrawalData['amount'] ?? 0, 0, ',', '.'),
                'bank_name' => $withdrawalData['bank_name'] ?? 'N/A',
                'account_number' => $withdrawalData['bank_account'] ?? 'N/A',
                'account_holder' => $withdrawalData['account_holder'] ?? 'N/A',
                'requested_at' => $withdrawalData['requested_at'] ?? date('Y-m-d H:i:s'),
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send withdrawal completed notification (to affiliate)
     */
    public function sendWithdrawalCompleted(string $email, string $name, array $withdrawalData): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Rút tiền thành công - {$withdrawalData['withdraw_code']}";
            
            $emailBody = $this->getEmailTemplate('withdrawal_completed', [
                'user_name' => $name,
                'withdraw_code' => $withdrawalData['withdraw_code'],
                'amount' => number_format($withdrawalData['amount'] ?? 0, 0, ',', '.'),
                'bank_name' => $withdrawalData['bank_name'] ?? 'N/A',
                'account_number' => $withdrawalData['bank_account'] ?? 'N/A',
                'completed_at' => date('d/m/Y H:i'),
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send withdrawal rejected notification
     */
    public function sendWithdrawalRejected(string $email, string $name, array $withdrawalData, string $reason): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Yêu cầu rút tiền bị từ chối - {$withdrawalData['withdraw_code']}";
            
            $emailBody = $this->getEmailTemplate('withdrawal_rejected', [
                'user_name' => $name,
                'withdraw_code' => $withdrawalData['withdraw_code'],
                'amount' => number_format($withdrawalData['amount'] ?? 0, 0, ',', '.'),
                'reason' => $reason,
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send OTP for bank info verification
     */
    public function sendBankInfoOTP(string $email, string $name, string $otp): bool {
        try {
            $this->setupMailer();
            $this->mailer->addAddress($email, $name);
            $this->mailer->Subject = "Mã xác thực thay đổi thông tin ngân hàng";
            
            $emailBody = $this->getEmailTemplate('otp_verification', [
                'user_name' => $name,
                'otp' => $otp,
                'expires_in' => '10 phút',
                'website_name' => 'ThuongLo'
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            return $result;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
}