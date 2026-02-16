<?php
/**
 * Agent Registration Processing Message View
 * Requirements: 1.5, 2.4, 4.4
 */

$messageData = $messageData ?? [];
$status = $status ?? 'pending';
$requestDetails = $requestDetails ?? [];

// Default message data based on status
if (empty($messageData)) {
    switch ($status) {
        case 'pending':
            $messageData = [
                'title' => 'Yêu cầu đang được xử lý',
                'message' => 'Yêu cầu đăng ký đại lý của bạn đang được xem xét. Chúng tôi sẽ phản hồi trong vòng 24 giờ.',
                'icon' => 'clock',
                'color' => 'warning'
            ];
            break;
        case 'approved':
            $messageData = [
                'title' => 'Chúc mừng! Yêu cầu đã được phê duyệt',
                'message' => 'Bạn đã trở thành đại lý của chúng tôi. Hãy truy cập trang đại lý để bắt đầu.',
                'icon' => 'check-circle',
                'color' => 'success'
            ];
            break;
        case 'rejected':
            $messageData = [
                'title' => 'Yêu cầu không được phê duyệt',
                'message' => 'Rất tiếc, yêu cầu đăng ký đại lý của bạn không được phê duyệt. Vui lòng liên hệ hỗ trợ để biết thêm chi tiết.',
                'icon' => 'x-circle',
                'color' => 'danger'
            ];
            break;
    }
}
?>

<div class="agent-processing-container">
    <div class="processing-card">
        <div class="processing-icon <?= htmlspecialchars($messageData['color']) ?>">
            <?php
            $icon = $messageData['icon'] ?? 'clock';
            switch ($icon) {
                case 'check-circle':
                    echo '✅';
                    break;
                case 'x-circle':
                    echo '❌';
                    break;
                case 'clock':
                default:
                    echo '⏰';
                    break;
            }
            ?>
        </div>
        
        <div class="processing-content">
            <h2 class="processing-title">
                <?= htmlspecialchars($messageData['title']) ?>
            </h2>
            
            <p class="processing-message">
                <?= htmlspecialchars($messageData['message']) ?>
            </p>
            
            <?php if (!empty($requestDetails)): ?>
            <div class="request-info">
                <h4>Thông tin yêu cầu</h4>
                
                <?php if (!empty($requestDetails['request_date'])): ?>
                <div class="info-row">
                    <span class="info-label">Ngày gửi:</span>
                    <span class="info-value">
                        <?= date('d/m/Y H:i', strtotime($requestDetails['request_date'])) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($requestDetails['email'])): ?>
                <div class="info-row">
                    <span class="info-label">Email liên hệ:</span>
                    <span class="info-value">
                        <?= htmlspecialchars($requestDetails['email']) ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($requestDetails['approved_date']) && $status === 'approved'): ?>
                <div class="info-row">
                    <span class="info-label">Ngày phê duyệt:</span>
                    <span class="info-value">
                        <?= date('d/m/Y H:i', strtotime($requestDetails['approved_date'])) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="processing-actions">
                <?php if ($status === 'approved'): ?>
                    <a href="?page=affiliate" class="action-btn primary">
                        <span>🎯</span>
                        Truy cập trang đại lý
                    </a>
                <?php elseif ($status === 'rejected'): ?>
                    <a href="?page=contact" class="action-btn primary">
                        <span>💬</span>
                        Liên hệ hỗ trợ
                    </a>
                <?php else: ?>
                    <button type="button" class="action-btn secondary" onclick="window.location.reload()">
                        <span>🔄</span>
                        Kiểm tra lại
                    </button>
                <?php endif; ?>
                
                <a href="<?= base_url() ?>" class="action-btn outline">
                    <span>🏠</span>
                    Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.agent-processing-container {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.processing-card {
    max-width: 600px;
    width: 100%;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 48px 40px;
    text-align: center;
    animation: slideInUp 0.6s ease-out;
}

.processing-icon {
    font-size: 72px;
    margin-bottom: 24px;
    display: block;
    animation: pulse 2s infinite;
}

.processing-icon.success {
    color: #28a745;
}

.processing-icon.warning {
    color: #ffc107;
}

.processing-icon.danger {
    color: #dc3545;
}

.processing-title {
    font-family: 'Inter', sans-serif;
    font-size: 1.75rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 16px;
    line-height: 1.3;
}

.processing-message {
    font-family: 'Inter', sans-serif;
    font-size: 1.1rem;
    color: #6c757d;
    line-height: 1.6;
    margin-bottom: 32px;
}

.request-info {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 32px;
    text-align: left;
}

.request-info h4 {
    font-family: 'Inter', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 16px;
    text-align: center;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    color: #495057;
    font-size: 14px;
}

.info-value {
    font-family: 'Inter', sans-serif;
    color: #6c757d;
    font-size: 14px;
    font-weight: 400;
}

.processing-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    border: none;
    border-radius: 10px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 140px;
    justify-content: center;
}

.action-btn.primary {
    background: #356DF1;
    color: #ffffff;
}

.action-btn.primary:hover {
    background: #2563EB;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(53, 109, 241, 0.3);
}

.action-btn.secondary {
    background: #6c757d;
    color: #ffffff;
}

.action-btn.secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.action-btn.outline {
    background: transparent;
    color: #6c757d;
    border: 2px solid #e9ecef;
}

.action-btn.outline:hover {
    background: #f8f9fa;
    color: #495057;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-btn span {
    font-size: 16px;
}

/* Animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .processing-card {
        padding: 32px 24px;
        margin: 20px;
    }
    
    .processing-icon {
        font-size: 56px;
        margin-bottom: 20px;
    }
    
    .processing-title {
        font-size: 1.5rem;
    }
    
    .processing-message {
        font-size: 1rem;
    }
    
    .processing-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
        min-width: auto;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding: 8px 0;
    }
    
    .request-info {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .agent-processing-container {
        padding: 20px 10px;
    }
    
    .processing-card {
        padding: 24px 16px;
    }
    
    .processing-title {
        font-size: 1.25rem;
    }
}
</style>