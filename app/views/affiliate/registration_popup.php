<?php
/**
 * Agent Registration Popup View
 * Requirements: 2.1, 2.2
 */
?>

<div class="agent-popup-overlay" id="agentRegistrationPopup">
    <div class="agent-popup">
        <div class="agent-popup-header">
            <h3>Đăng ký trở thành đại lý</h3>
            <button type="button" class="close-popup" onclick="closeAgentPopup()">&times;</button>
        </div>
        
        <div class="agent-popup-content">
            <form id="agentRegistrationForm" method="POST" action="/api/agent/register">
                <?php if (isset($csrf_token)): ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="agent_email">Email Gmail (bắt buộc) *</label>
                    <input 
                        type="email" 
                        id="agent_email" 
                        name="agent_email" 
                        required 
                        pattern=".*@gmail\.com$" 
                        title="Vui lòng nhập địa chỉ Gmail hợp lệ"
                        placeholder="example@gmail.com"
                        value="<?= htmlspecialchars($current_email ?? '') ?>"
                    >
                    <small class="form-help">
                        Chỉ chấp nhận địa chỉ Gmail để đảm bảo liên lạc hiệu quả
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="agent_info">Thông tin bổ sung</label>
                    <textarea 
                        id="agent_info" 
                        name="agent_info" 
                        rows="3" 
                        placeholder="Kinh nghiệm, lý do muốn trở thành đại lý..."
                    ></textarea>
                </div>
                
                <div class="info-notice">
                    <div class="info-icon">ℹ️</div>
                    <div class="info-text">
                        <strong>Quy trình xử lý:</strong>
                        <ul>
                            <li>Yêu cầu sẽ được xem xét trong vòng 24 giờ</li>
                            <li>Chúng tôi sẽ gửi email thông báo kết quả</li>
                            <li>Sau khi được phê duyệt, bạn sẽ có quyền truy cập đầy đủ các tính năng đại lý</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAgentPopup()" class="btn-cancel">Hủy</button>
                    <button type="submit" class="btn-submit">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>