/**
 * user_access.js - Xử lý logic quản lý thiết bị và xác thực OTP
 */

document.addEventListener('DOMContentLoaded', function () {
    // --- KHỞI TẠO BIẾN ---
    console.log('user_access.js loaded');
    
    const pendingDeviceId = new URLSearchParams(window.location.search).get('device_session_id');
    const isVerifyPage = new URLSearchParams(window.location.search).get('device_verify') === '1';

    let otpCooldownTimer = null;
    let pollInterval = null;

    // --- XỬ LÝ TRÊN TRANG LOGIN (XÁC THỰC OTP) ---
    if (isVerifyPage) {
        console.log('Init device verification');
        initDeviceVerification();
    }

    // --- XỬ LÝ TRÊN TRANG QUẢN LÝ TRUY CẬP (ACCESS PAGE) ---
    console.log('Init access management');
    initAccessManagement();

    // ==========================================
    // LOGIC TRANG QUẢN LÝ TRUY CẬP
    // ==========================================
    function initAccessManagement() {
        console.log('initAccessManagement called');
        
        const btnApprove = document.querySelectorAll('.btn-approve');
        const btnReject = document.querySelectorAll('.btn-reject');
        const btnRemove = document.querySelectorAll('.btn-remove');
        
        console.log('Found buttons - approve:', btnApprove.length, 'reject:', btnReject.length, 'remove:', btnRemove.length);
        
        const passwordModal = document.getElementById('passwordConfirmModal');
        const btnConfirmApprove = document.getElementById('btnConfirmApprove');

        let targetDeviceId = null;

        // Bấm nút Phê duyệt
        btnApprove.forEach(btn => {
            btn.addEventListener('click', function () {
                targetDeviceId = this.getAttribute('data-id');
                const deviceName = this.getAttribute('data-name');
                document.getElementById('targetDeviceName').textContent = deviceName;

                const modal = new bootstrap.Modal(passwordModal);
                modal.show();
            });
        });

        // Xác nhận mật khẩu để phê duyệt
        if (btnConfirmApprove) {
            btnConfirmApprove.addEventListener('click', function () {
                const password = document.getElementById('confirmPassword').value;
                const password2 = document.getElementById('confirmPassword2').value;
                const passwordInput = document.getElementById('confirmPassword');
                const password2Input = document.getElementById('confirmPassword2');
                const passwordError = document.getElementById('passwordError');
                const password2Error = document.getElementById('password2Error');
                
                // Reset validation
                passwordInput.classList.remove('is-invalid');
                password2Input.classList.remove('is-invalid');
                
                if (!password) {
                    passwordInput.classList.add('is-invalid');
                    passwordError.textContent = 'Vui lòng nhập mật khẩu.';
                    return;
                }
                
                if (!password2) {
                    password2Input.classList.add('is-invalid');
                    password2Error.textContent = 'Vui lòng nhập lại mật khẩu.';
                    return;
                }
                
                if (password !== password2) {
                    password2Input.classList.add('is-invalid');
                    password2Error.textContent = 'Mật khẩu không khớp.';
                    return;
                }

                btnConfirmApprove.disabled = true;
                btnConfirmApprove.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

                fetch('api.php?path=device/approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_session_id: targetDeviceId, password: password })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Đã phê duyệt thiết bị thành công!');
                            // Close modal
                            const modalEl = document.getElementById('passwordConfirmModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                            location.reload();
                        } else {
                            passwordInput.classList.add('is-invalid');
                            passwordError.textContent = data.message || 'Mật khẩu không đúng.';
                        }
                    })
                    .finally(() => {
                        btnConfirmApprove.disabled = false;
                        btnConfirmApprove.textContent = 'Xác nhận';
                    });
            });
        }

        // Bấm nút Từ chối
        btnReject.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                if (!confirm('Bạn có chắc chắn muốn từ chối thiết bị này?')) return;

                fetch('api.php?path=device/reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_session_id: id })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            });
        });

        // Bấm nút Xóa thiết bị
        btnRemove.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const isCurrent = this.getAttribute('data-current') === '1';

                console.log('Delete device:', id, 'isCurrent:', isCurrent);

                let msg = 'Bạn có chắc chắn muốn xóa thiết bị này?';
                if (isCurrent) {
                    msg = 'Đây là thiết bị bạn đang sử dụng. Nếu xóa, bạn sẽ bị đăng xuất ngay lập tức. Tiếp tục?';
                }

                if (!confirm(msg)) return;

                fetch('api.php?path=device/remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_id: id })
                })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Remove response:', data);
                        if (data.success) {
                            if (data.should_logout) {
                                console.log('Redirecting to logout...');
                                window.location.href = '?page=logout';
                            } else {
                                console.log('Reloading page...');
                                location.reload();
                            }
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                    });
            });
        });

        // Tự động reload nếu có pending request để cập nhật trạng thái (Polling)
        if (document.querySelector('.pending-requests-section')) {
            setInterval(() => {
                fetch('api.php?path=device/pending')
                    .then(res => res.json())
                    .then(data => {
                        // Nếu số lượng pending thay đổi so với hiện tại -> reload
                        const currentPendingCount = document.querySelectorAll('.pending-card').length;
                        if (data.devices && data.devices.length !== currentPendingCount) {
                            location.reload();
                        }
                    });
            }, 10000); // Mỗi 10 giây
        }
    }

    // ==========================================
    // LOGIC TRÊN TRANG LOGIN (POPUP XÁC THỰC)
    // ==========================================
    function initDeviceVerification() {
        // Tạo HTML cho popup xác thực nếu chưa có
        if (!document.getElementById('deviceVerifyModal')) {
            injectVerifyModal();
        }

        const modalElement = document.getElementById('deviceVerifyModal');
        const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
        modal.show();

        const btnSendOtp = document.getElementById('btnSendOtp');
        const btnVerifyOtp = document.getElementById('btnVerifyOtp');
        const btnResendOtp = document.getElementById('btnResendOtp');
        const emailInput = document.getElementById('verifyEmail');
        const otpInput = document.getElementById('otpCode');

        // Gửi mã OTP lần đầu
        btnSendOtp.addEventListener('click', function () {
            const email = emailInput.value;
            if (!email) {
                alert('Vui lòng nhập email của bạn.');
                return;
            }

            btnSendOtp.disabled = true;
            btnSendOtp.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';

            fetch('api.php?path=device/verify-email', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, device_session_id: pendingDeviceId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('emailStep').classList.add('d-none');
                        document.getElementById('otpStep').classList.remove('d-none');
                        document.getElementById('displayMaskedEmail').textContent = data.masked_email;
                        startOtpCooldown(data.cooldown);
                    } else {
                        alert(data.message);
                    }
                })
                .finally(() => {
                    btnSendOtp.disabled = false;
                    btnSendOtp.textContent = 'Gửi mã xác thực';
                });
        });

        // Xác thực mã OTP
        btnVerifyOtp.addEventListener('click', function () {
            const code = otpInput.value;
            if (code.length !== 6) {
                alert('Mã OTP phải gồm 6 chữ số.');
                return;
            }

            btnVerifyOtp.disabled = true;
            btnVerifyOtp.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';

            fetch('api.php?path=device/verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code: code, device_session_id: pendingDeviceId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Xác thực thành công! Đang chuyển hướng...');
                        window.location.href = '?page=users';
                    } else {
                        alert(data.message);
                        if (data.attempts_left !== undefined) {
                            document.getElementById('otpAttemptsTip').textContent = `Bạn còn ${data.attempts_left} lần thử.`;
                        }
                    }
                })
                .finally(() => {
                    btnVerifyOtp.disabled = false;
                    btnVerifyOtp.textContent = 'Xác nhận đăng nhập';
                });
        });

        // Gửi lại mã OTP
        btnResendOtp.addEventListener('click', function () {
            if (btnResendOtp.disabled) return;

            btnResendOtp.disabled = true;
            fetch('api.php?path=device/resend-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_session_id: pendingDeviceId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Mã mới đã được gửi!');
                        startOtpCooldown(data.cooldown);
                    } else {
                        alert(data.message);
                        btnResendOtp.disabled = false;
                    }
                });
        });

        // Bắt đầu polling kiểm tra xem có được duyệt từ thiết bị khác không
        pollInterval = setInterval(() => {
            fetch(`api.php?path=device/poll-status&device_session_id=${pendingDeviceId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === 'active') {
                            clearInterval(pollInterval);
                            alert('Thiết bị đã được phê duyệt! Đang chuyển hướng...');
                            // Lưu user_id vào session thực sự (cần API hỗ trợ login sau approve)
                            // Trong api.php device/verify-otp đã handle việc clone session pending
                            // Ở đây chúng ta cần gọi 1 endpoint để "hoàn tất" login nếu được approve
                            // Hoặc đơn giản là refresh trang login, AuthService sẽ thấy device đã active
                            window.location.href = '?page=users';
                        } else if (data.status === 'rejected') {
                            clearInterval(pollInterval);
                            alert('Yêu cầu truy cập bị từ chối.');
                            window.location.href = '?page=login';
                        }
                    }
                });
        }, 5000); // 5 giây một lần
    }

    function startOtpCooldown(seconds) {
        let timeLeft = seconds;
        const btn = document.getElementById('btnResendOtp');
        const timerText = document.getElementById('resendTimerText');

        btn.disabled = true;

        if (otpCooldownTimer) clearInterval(otpCooldownTimer);

        otpCooldownTimer = setInterval(() => {
            timeLeft--;
            timerText.textContent = `(${timeLeft}s)`;

            if (timeLeft <= 0) {
                clearInterval(otpCooldownTimer);
                btn.disabled = false;
                timerText.textContent = '';
            }
        }, 1000);
    }

    function injectVerifyModal() {
        const modalHtml = `
            <div class="modal fade" id="deviceVerifyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title"><i class="fas fa-shield-alt me-2 text-warning"></i> Xác thực thiết bị mới</h5>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Tabs Nav -->
                            <ul class="nav nav-pills nav-justified mb-4" id="verifyTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#emailVerifyTab">Xác thực Email</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#remoteVerifyTab">Phê duyệt khác</button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Email Tab -->
                                <div class="tab-pane fade show active" id="emailVerifyTab">
                                    <div id="emailStep">
                                        <p class="text-muted">Nhập email đăng ký tài khoản để nhận mã OTP 6 số.</p>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Email tài khoản</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" id="verifyEmail" class="form-control" placeholder="example@gmail.com">
                                            </div>
                                        </div>
                                        <button id="btnSendOtp" class="btn btn-primary w-100 py-2">Gửi mã xác thực</button>
                                    </div>

                                    <div id="otpStep" class="d-none">
                                        <div class="alert alert-info py-2 small">
                                            Mã đã được gửi đến: <strong id="displayMaskedEmail"></strong>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Nhập mã OTP 6 số</label>
                                            <input type="text" id="otpCode" class="form-control text-center fs-3 fw-bold" maxlength="6" placeholder="000000" style="letter-spacing: 12px;">
                                            <p id="otpAttemptsTip" class="text-muted small mt-1"></p>
                                        </div>
                                        <button id="btnVerifyOtp" class="btn btn-success w-100 py-2 mb-3">Xác nhận đăng nhập</button>
                                        <button id="btnResendOtp" class="btn btn-link btn-sm w-100 text-decoration-none">Gửi lại mã <span id="resendTimerText"></span></button>
                                    </div>
                                </div>

                                <!-- Remote Tab -->
                                <div class="tab-pane fade" id="remoteVerifyTab">
                                    <div class="text-center py-4">
                                        <div class="spinner-grow text-warning mb-3" role="status"></div>
                                        <h5>Đang đợi phê duyệt từ thiết bị khác</h5>
                                        <p class="text-muted px-3">Hãy mở website trên một thiết bị bạn đã đăng nhập trước đó và phê duyệt yêu cầu này trong mục <strong>Truy cập</strong>.</p>
                                        <div class="alert alert-warning small mt-3 mx-2">
                                            <i class="fas fa-info-circle"></i> Trình duyệt này sẽ tự động chuyển hướng sau khi được phê duyệt.
                                        </div>
                                        <a href="?page=login" class="btn btn-outline-secondary btn-sm mt-2">Quay lại đăng nhập</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
});
