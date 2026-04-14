<?php
/**
 * EmailNotificationService - Service gửi email thông báo
 * Triển khai service gửi email sử dụng PHPMailer hiện có
 * Requirements: 1.4, 2.3, 3.4, 5.1, 5.2, 5.3, 5.4
 */

require_once __DIR__ . '/ServiceInterface.php';
require_once __DIR__ . '/../../assets/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../assets/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../assets/vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailNotificationService implements ServiceInterface {
    private $mailer;
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
     * Gửi email thông báo từ chối yêu cầu đăng ký đại lý
     */
    public function sendRejectionNotification(string $userEmail, string $userName, string $reason = ''): bool {
        try {
            $this->setupMailer();
            
            // Recipient
            $this->mailer->addAddress($userEmail, $userName);
            
            // Content
            $this->mailer->Subject = 'Yeu cau dang ky dai ly chua duoc duyet - ThuongLo';
            
            $emailBody = $this->getEmailTemplate('rejection_notification', [
                'user_name' => $userName,
                'reason' => $reason ?: 'Chua dap ung du dieu kien dang ky dai ly tai thoi diem hien tai',
                'reapply_info' => 'Ban co the gui lai yeu cau dang ky dai ly bat cu luc nao',
                'contact_email' => $this->emailConfig['support_email'],
                'website_name' => 'ThuongLo',
                'website_url' => $this->getBaseUrl()
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Rejection email failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * G sending email thông báo hoa hông
     */
    public function sendCommissionEarned(string $userEmail, string $userName, float $commission, string $orderNumber): bool {
        try {
            $this->setupMailer();
            
            // Recipient
            $this->mailer->addAddress($userEmail, $userName);
            
            // Content
            $this->mailer->Subject = 'Ban nhan duoc hoa hong moi - ThuongLo';
            
            $emailBody = $this->getEmailTemplate('commission_earned', [
                'user_name' => $userName,
                'commission_amount' => number_format($commission, 0, ',', '.') . ' VNÄ',
                'order_number' => $orderNumber,
                'website_name' => 'ThuongLo',
                'website_url' => $this->getBaseUrl()
            ]);
            
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);
            
            $result = $this->mailer->send();
            $this->resetMailer();
            
            return $result;
            
        } catch (PHPMailerException $e) {
            error_log('PHPMailer Error in sendCommissionEarned: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log('General Error in sendCommissionEarned: ' . $e->getMessage());
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
                $this->mailer->SMTPSecure = 'tls';
            } elseif ($smtpSecure === 'ssl') {
                $this->mailer->SMTPSecure = 'ssl';
            } else {
                $this->mailer->SMTPSecure = $smtpSecure;
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
            'rejection_notification' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #dc3545;">&#10060; Thông báo từ chối yêu cầu</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Chúng tôi rất tiếc phải thông báo rằng yêu cầu đăng ký làm đại lý của bạn <strong>chưa được duyệt</strong>.</p>
                    
                    <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                        <h3 style="color: #721c24; margin-top: 0;">Lý do:</h3>
                        <p style="color: #721c24; margin-bottom: 0;">{{reason}}</p>
                    </div>
                    
                    <div style="background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #17a2b8;">
                        <h3 style="color: #0c5460; margin-top: 0;">Bạn vẫn có thể đăng ký lại!</h3>
                        <p style="color: #0c5460; margin-bottom: 0;">{{reapply_info}}</p>
                    </div>
                    
                    <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi để được hỗ trợ.</p>
                    <p>Liên hệ hỗ trợ: <a href="mailto:{{contact_email}}">{{contact_email}}</a></p>
                    <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}<br><a href="{{website_url}}">{{website_url}}</a></p>
                </div>
            ',
            'commission_earned' => '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #28a745;">&#127874; Chúc mùng! Ban nhan duoc hoa hong moi!</h2>
                    <p>Xin chào <strong>{{user_name}}</strong>,</p>
                    <p>Ban vua nhan duoc hoa hong <strong style="color: #28a745;">{{commission_amount}}</strong> tu don hang <strong>{{order_number}}</strong>.</p>
                    
                    <div style="background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
                        <h3 style="color: #155724; margin-top: 0;">Chi tiet hoa hong:</h3>
                        <ul style="color: #155724;">
                            <li>So tien hoa hong: {{commission_amount}}</li>
                            <li>Ma don hang: {{order_number}}</li>
                            <li>Trang thai: Da cong vao vi</li>
                        </ul>
                    </div>
                    
                    <p>Ban co the <a href="{{website_url}}/affiliate/dashboard" style="color: #007bff;">xem chi tiet</a> trong bang dieu khien dai ly.</p>
                    <p>Cam on ban da kinh doanh voi {{website_name}}!</p>
                    <p style="color: #6c757d; font-size: 14px;">Trân truong,<br>Doi ngu {{website_name}}</p>
                </div>
            ',
            'device_verification' => '<div><h2>Xac thuc dang nhap</h2><p>Xin chao {{user_name}},</p><p>Ma xac thuc cua ban la: <strong>{{verification_code}}</strong></p><p>Ma co hieu luc trong {{expiry_minutes}} phut</p></div>'
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
                $this->mailer->Debugoutput = function($str, $level) {
                    return $str; // Capture debug output
                };
            }
                <div style="background-color: #f8f9fa; padding: 15px; border-radius:5px; margin: 20px 0;">
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
                
                <div style="background-color: #d4edda; padding: 15px; border-radius:5px; margin: 20px 0; border-left: 4px solid #28a745;">
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
                
                <div style="background-color: #fff3cd; padding: 15px; border-radius:5px; margin: 20px 0; border-left: 4px solid #ffc107;">
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
        'rejection_notification' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #dc3545;">&#10060; Thông báo từ chối yêu cầu</h2>
                <p>Xin chào <strong>{{user_name}}</strong>,</p>
                <p>Chúng tôi rất tiếc phải thông báo rằng yêu cầu đăng ký làm đại lý của bạn <strong>chưa được duyệt</strong>.</p>
                
                <div style="background-color: #f8d7da; padding: 15px; border-radius:5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                    <h3 style="color: #721c24; margin-top: 0;">Lý do:</h3>
                    <p style="color: #721c24; margin-bottom: 0;">{{reason}}</p>
                </div>
                
                <div style="background-color: #d1ecf1; padding: 15px; border-radius:5px; margin: 20px 0; border-left: 4px solid #17a2b8;">
                    <h3 style="color: #0c5460; margin-top: 0;">Bạn vẫn có thể đăng ký lại!</h3>
                    <p style="color: #0c5460; margin-bottom: 0;">{{reapply_info}}</p>
                </div>
                
                <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi để được hỗ trợ.</p>
                <p>Liên hệ hỗ trợ: <a href="mailto:{{contact_email}}">{{contact_email}}</a></p>
                <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}<br><a href="{{website_url}}">{{website_url}}</a></p>
            </div>
        ',
        'commission_earned' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #28a745;">&#127874; Chúc mùng! Ban nhan duoc hoa hong moi!</h2>
                <p>Xin chào <strong>{{user_name}}</strong>,</p>
                <p>Ban vua nhan duoc hoa hong <strong style="color: #28a745;">{{commission_amount}}</strong> tu don hang <strong>{{order_number}}</strong>.</p>
                
                <div style="background-color: #d4edda; padding: 15px; border-radius:5px; margin: 20px 0; border-left: 4px solid #28a745;">
                    <h3 style="color: #155724; margin-top: 0;">Chi tiet hoa hong:</h3>
                    <ul style="color: #155724;">
                        <li>So tien hoa hong: {{commission_amount}}</li>
                        <li>Ma don hang: {{order_number}}</li>
                        <li>Trang thai: Da cong vao vi</li>
                    </ul>
                </div>
                
                <p>Ban co <a href="{{website_url}}/affiliate/dashboard" style="color: #007bff;">xem chi tiet</a> trong bang dieu khien dai ly.</p>
                <p>Cam on ban da kinh doanh voi {{website_name}}!</p>
                <p style="color: #6c757d; font-size: 14px;">Trân truong,<br>Doi ngu {{website_name}}</p>
            </div>
        ',
        'device_verification' => '<div><h2>Xac thuc dang nhap</h2><p>Xin chao {{user_name}},</p><p>Ma xac thuc cua ban la: <strong>{{verification_code}}</strong></p><p>Ma co hieu luc trong {{expiry_minutes}} phut</p></div>',
        
        'withdrawal_approved' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #28a745;">&#127874; Chúc mừng! Yêu cầu rút tiền đã được duyệt!</h2>
                <p>Xin chào <strong>{{user_name}}</strong>,</p>
                <p>Yêu cầu rút tiền của bạn đã được xử lý thành công.</p>
                
                <div style="background-color: #d4edda; padding:15px; border-radius:5px; margin: 20px 0; border-left: 4px solid #28a745;">
                    <h3 style="color: #155724; margin-top: 0;">Chi tiết giao dịch:</h3>
                    <ul style="color: #155724;">
                        <li>Mã rút tiền: {{withdraw_code}}</li>
                        <li>Số tiền: {{amount}} VNĐ</li>
                        <li>Ngân hàng: {{bank_name}}</li>
                        <li>Chủ tài khoản: {{account_holder}}</li>
                        <li>Mã giao dịch: {{transaction_id}}</li>
                    </ul>
                </div>
                
                <p>Bạn có thể <a href="{{website_url}}/affiliate/dashboard" style="color: #007bff;">xem chi tiết</a> trong bảng điều khiển đại lý.</p>
                <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
                <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
            </div>
        ',

        'withdrawal_rejected' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #dc3545;">&#10060; Yêu cầu rút tiền bị từ chối</h2>
                <p>Xin chào <strong>{{user_name}}</strong>,</p>
                <p>Yêu cầu rút tiền của bạn không thể được xử lý.</p>
                
                <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                    <h3 style="color: #721c24; margin-top: 0;">Lý do từ chối:</h3>
                    <p style="color: #721c24; margin-bottom: 0;">{{reason}}</p>
                </div>
                
                <p>Bạn có thể tạo yêu cầu rút tiền mới sau khi đã giải quyết các vấn đề.</p>
                <p>Nếu có thắc mắc, vui lòng liên hệ với chúng tôi để được hỗ trợ.</p>
                <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
            </div>
        ',

        'withdrawal_cancelled' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #ffc107;">&#9203; Yêu cầu rút tiền đã bị hủy</h2>
                <p>Xin chào <strong>{{user_name}}</strong>,</p>
                <p>Yêu cầu rút tiền của bạn đã bị hủy.</p>
                
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                    <h3 style="color: #856404; margin-top: 0;">Chi tiết yêu cầu:</h3>
                    <ul style="color: #856404;">
                        <li>Mã rút tiền: {{withdraw_code}}</li>
                        <li>Số tiền: {{amount}} VNĐ</li>
                    </ul>
                </div>
                
                <p>Số tiền đã được hoàn trả vào tài khoản đại lý của bạn.</p>
                <p>Bạn có thể tạo yêu cầu rút tiền mới bất cứ lúc nào.</p>
                <p style="color: #6c757d; font-size: 14px;">Trân trọng,<br>Đội ngũ {{website_name}}</p>
            </div>
        ',
    ];
}

// ... (rest of the code remains the same)

/**
 * Gửi email thông báo duyệt yêu cầu rút tiền
 */
public function sendWithdrawalApprovedEmail(string $email, array $data): bool {
    try {
        $this->setupMailer();
        
        $this->mailer->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
        $this->mailer->addAddress($email);
        
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Yêu cầu rút tiền đã được duyệt - ' . $data['withdraw_code'];
        
        $template = $this->templates['withdrawal_approved'] ?? $this->getDefaultWithdrawalApprovedTemplate();
        $body = $this->replaceTemplateVariables($template, array_merge($data, [
            'recipient_email' => $email,
            'site_name' => $this->emailConfig['site_name'] ?? 'ThuongLo',
            'support_email' => $this->emailConfig['support_email'] ?? $this->emailConfig['from_email']
        ]));
        
        $this->mailer->Body = $body;
        
        $result = $this->mailer->send();
        error_log("Withdrawal approved email sent to $email: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Withdrawal approved email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi email thông báo từ chối yêu cầu rút tiền
 */
public function sendWithdrawalRejectedEmail(string $email, array $data): bool {
    try {
        $this->setupMailer();
        
        $this->mailer->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
        $this->mailer->addAddress($email);
        
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Yêu cầu rút tiền đã bị từ chối - ' . $data['withdraw_code'];
        
        $template = $this->templates['withdrawal_rejected'] ?? $this->getDefaultWithdrawalRejectedTemplate();
        $body = $this->replaceTemplateVariables($template, array_merge($data, [
            'recipient_email' => $email,
            'site_name' => $this->emailConfig['site_name'] ?? 'ThuongLo',
            'support_email' => $this->emailConfig['support_email'] ?? $this->emailConfig['from_email']
        ]));
        
        $this->mailer->Body = $body;
        
        $result = $this->mailer->send();
        error_log("Withdrawal rejected email sent to $email: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Withdrawal rejected email failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi email thông báo hủy yêu cầu rút tiền
 */
public function sendWithdrawalCancelledEmail(string $email, array $data): bool {
    try {
        $this->setupMailer();
        
        $this->mailer->setFrom($this->emailConfig['from_email'], $this->emailConfig['from_name']);
        $this->mailer->addAddress($email);
        
        $this->mailer->isHTML(true);
        $this->mailer->Subject = 'Yêu cầu rút tiền đã bị hủy - ' . $data['withdraw_code'];
        
        $template = $this->templates['withdrawal_cancelled'] ?? $this->getDefaultWithdrawalCancelledTemplate();
        $body = $this->replaceTemplateVariables($template, array_merge($data, [
            'recipient_email' => $email,
            'site_name' => $this->emailConfig['site_name'] ?? 'ThuongLo',
            'support_email' => $this->emailConfig['support_email'] ?? $this->emailConfig['from_email']
        ]));
        
        $this->mailer->Body = $body;
        
        $result = $this->mailer->send();
        error_log("Withdrawal cancelled email sent to $email: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Withdrawal cancelled email failed: " . $e->getMessage());
        return false;
    }
}

// ... (rest of the code remains the same)