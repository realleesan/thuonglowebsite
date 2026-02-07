<?php
/**
 * Affiliate Footer
 * Design System: Giống Admin
 */
?>

<footer class="affiliate-footer">
    <div class="footer-content">
        <!-- Left Side - Copyright -->
        <div class="footer-left">
            <p class="copyright">
                © <?php echo date('Y'); ?> <strong>ThuongLo</strong> Affiliate System. 
                Tất cả quyền được bảo lưu. 
                Được phát triển bởi <a href="https://mistydev.id.vn/" target="_blank">Misty Team</a>.
            </p>
        </div>

        <!-- Right Side - Links -->
        <div class="footer-right">
            <div class="footer-info">
                <span class="version">v1.0.0</span>
                <span class="separator">|</span>
                <a href="<?php echo base_url(); ?>?page=help" class="footer-link">Trợ giúp</a>
                <span class="separator">|</span>
                <a href="<?php echo base_url(); ?>?page=terms" class="footer-link">Điều khoản</a>
                <span class="separator">|</span>
                <a href="<?php echo base_url(); ?>?page=privacy" class="footer-link">Bảo mật</a>
            </div>
        </div>
    </div>
</footer>
