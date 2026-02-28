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
        
        // Thêm các hàm đóng modal toàn cục
        window.closeApproveConfirmModal = function() {
            const confirmModal = document.getElementById('approveConfirmModal');
            if (confirmModal) {
                confirmModal.classList.remove('active');
            }
        };
        
        window.closePasswordModal = function() {
            const modalEl = document.getElementById('passwordConfirmModal');
            if (modalEl) {
                modalEl.classList.remove('active');
            }
        };
        
        const btnApprove = document.querySelectorAll('.btn-approve');
        const btnReject = document.querySelectorAll('.btn-reject');
        const btnRemove = document.querySelectorAll('.btn-remove');
        
        console.log('Found buttons - approve:', btnApprove.length, 'reject:', btnReject.length, 'remove:', btnRemove.length);
        
        const passwordModal = document.getElementById('passwordConfirmModal');
        const btnConfirmApprove = document.getElementById('btnConfirmApprove');

        let targetDeviceId = null;

        // Bấm nút Phê duyệt - Hiện modal xác nhận trước
        btnApprove.forEach(btn => {
            btn.addEventListener('click', function () {
                targetDeviceId = this.getAttribute('data-id');
                const deviceName = this.getAttribute('data-name');
                const deviceInfo = this.getAttribute('data-info');
                
                // Hiển thị thông tin trong modal xác nhận
                document.getElementById('confirmDeviceName').textContent = deviceName;
                document.getElementById('confirmDeviceInfo').textContent = deviceInfo || '';
                document.getElementById('targetDeviceName').textContent = deviceName;

                // Show confirm modal first
                const confirmModal = document.getElementById('approveConfirmModal');
                if (confirmModal) {
                    confirmModal.classList.add('active');
                }
            });
        });
        
        // Bấm nút Đồng ý trong modal xác nhận -> Hiện modal mật khẩu
        const btnConfirmDevice = document.getElementById('btnConfirmDevice');
        if (btnConfirmDevice) {
            btnConfirmDevice.addEventListener('click', function() {
                // Close confirm modal
                const confirmModal = document.getElementById('approveConfirmModal');
                if (confirmModal) {
                    confirmModal.classList.remove('active');
                }
                
                // Reset password fields
                document.getElementById('confirmPassword').value = '';
                document.getElementById('confirmPassword2').value = '';
                
                // Show password modal
                const modalEl = document.getElementById('passwordConfirmModal');
                if (modalEl) {
                    modalEl.classList.add('active');
                }
            });
        }

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
                passwordError.classList.remove('show');
                password2Error.classList.remove('show');
                
                if (!password) {
                    passwordInput.classList.add('is-invalid');
                    passwordError.classList.add('show');
                    passwordError.textContent = 'Vui lòng nhập mật khẩu.';
                    return;
                }
                
                if (!password2) {
                    password2Input.classList.add('is-invalid');
                    password2Error.classList.add('show');
                    password2Error.textContent = 'Vui lòng nhập lại mật khẩu.';
                    return;
                }
                
                if (password !== password2) {
                    password2Input.classList.add('is-invalid');
                    password2Error.classList.add('show');
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
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Network response was not ok: ' + res.status);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Approve response:', data);
                        if (data.debug) {
                            console.log('Debug info:', data.debug);
                        }
                        if (data.success) {
                            alert('Đã phê duyệt thiết bị thành công! Thiết bị này sẽ đăng nhập và bạn sẽ bị đăng xuất.');
                            // Close custom modal
                            const modalEl = document.getElementById('passwordConfirmModal');
                            if (modalEl) {
                                modalEl.classList.remove('active');
                            }
                            location.reload();
                        } else {
                            passwordInput.classList.add('is-invalid');
                            passwordError.classList.add('show');
                            passwordError.textContent = data.message || 'Mật khẩu không đúng.';
                        }
                    })
                    .catch(error => {
                        console.error('Approve error:', error);
                        alert('Có lỗi xảy ra: ' + error.message);
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

                // Disable button to prevent double click
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

                fetch('api.php?path=device/reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_session_id: id })
                })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Ẩn thiết bị
                            const deviceEl = document.getElementById('device-' + id);
                            if (deviceEl) {
                                deviceEl.style.display = 'none';
                            }
                            setTimeout(() => location.reload(), 500);
                        } else {
                            alert(data.message || 'Có lỗi xảy ra.');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-times"></i> Từ chối';
                        }
                    })
                    .catch(error => {
                        console.error('Reject error:', error);
                        // Vẫn reload vì có thể API đã xử lý
                        location.reload();
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

        // Inject modal HTML
        injectVerifyModal();

        // ========== Step Navigation ==========
        const step1 = document.getElementById('verifyStep1');
        const step2 = document.getElementById('verifyStep2');
        const emailStep = document.getElementById('verifyEmailStep');
        const remoteStep = document.getElementById('verifyRemoteStep');
        const emailInputStep = document.getElementById('emailInputStep');
        const otpInputStep = document.getElementById('otpInputStep');

        // ========== Button References ==========
        const btnSendOtp = document.getElementById('btnSendOtp');
        const btnVerifyOtp = document.getElementById('btnVerifyOtp');
        const btnResendOtp = document.getElementById('btnResendOtp');
        const emailInput = document.getElementById('deviceVerifyEmail');
        const otpInput = document.getElementById('deviceVerifyOtpCode');

        // Step 1: Start verification
        document.getElementById('btnStartVerify').addEventListener('click', function() {
            step1.classList.add('d-none-custom');
            step2.classList.remove('d-none-custom');
        });

        // Step 2: Confirm method selection
        document.getElementById('btnConfirmMethod').addEventListener('click', function() {
            const selectedMethod = document.querySelector('input[name="verifyMethod"]:checked').value;
            
            if (selectedMethod === 'email') {
                step2.classList.add('d-none-custom');
                emailStep.classList.remove('d-none-custom');
            } else {
                step2.classList.add('d-none-custom');
                remoteStep.classList.remove('d-none-custom');
                // Start polling for approval
                startApprovalPolling();
            }
        });
        
        // Add visual feedback for radio selection
        const radioOptions = document.querySelectorAll('.radio-option');
        radioOptions.forEach(option => {
            option.addEventListener('click', function() {
                radioOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Back to Step 1 from Step 2
        document.getElementById('btnBackStep1').addEventListener('click', function() {
            step2.classList.add('d-none-custom');
            step1.classList.remove('d-none-custom');
        });

        // Back to Step 2 from Email Step
        document.getElementById('btnBackToOptions').addEventListener('click', function() {
            emailStep.classList.add('d-none-custom');
            step2.classList.remove('d-none-custom');
        });

        // Back to Step 2 from Remote Step
        document.getElementById('btnBackFromRemote').addEventListener('click', function() {
            remoteStep.classList.add('d-none-custom');
            step2.classList.remove('d-none-custom');
        });

        // Function to show email OTP input after sending
        function showOtpInput(maskedEmail) {
            document.getElementById('displayMaskedEmail').textContent = maskedEmail;
            emailInputStep.classList.add('d-none');
            otpInputStep.classList.remove('d-none');
        }

        // ========== OTP Functions ==========
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
                        showOtpInput(data.masked_email);
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

        // Function to start polling for remote approval
        function startApprovalPolling() {
            if (pollInterval) clearInterval(pollInterval);
            
            pollInterval = setInterval(() => {
                fetch(`api.php?path=device/poll-status&device_session_id=${pendingDeviceId}`)
                .then(res => res.json())
                .then(data => {
                    console.log('Poll status:', data);
                    if (data.success) {
                        if (data.status === 'active') {
                            clearInterval(pollInterval);
                            // Đóng modal trước khi gọi API đăng nhập
                            hideModal();
                            
                            // Gọi API đăng nhập tự động
                            fetch('api.php?path=device/auto-login', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ device_session_id: pendingDeviceId })
                            })
                            .then(res => res.json())
                            .then(loginData => {
                                console.log('Auto login result:', loginData);
                                if (loginData.success) {
                                    setTimeout(() => {
                                        alert('Đăng nhập thành công! Đang chuyển hướng...');
                                        window.location.href = '?page=users';
                                    }, 300);
                                } else {
                                    alert('Đăng nhập thất bại: ' + loginData.message);
                                    window.location.href = '?page=login';
                                }
                            })
                            .catch(err => {
                                console.error('Auto login error:', err);
                                alert('Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.');
                                window.location.href = '?page=login';
                            });
                        } else if (data.status === 'rejected') {
                            clearInterval(pollInterval);
                            // Đóng modal trước khi alert
                            hideModal();
                            setTimeout(() => {
                                alert('Đăng nhập thất bại: Thiết bị của bạn đã bị từ chối. Vui lòng thử lại hoặc liên hệ quản trị viên.');
                                // Xóa các tham số device_verify và chuyển về trang login thuần
                                window.location.href = '?page=login';
                            }, 300);
                        }
                    }
                })
                .catch(err => {
                    console.error('Poll error:', err);
                });
            }, 5000); // 5 giây một lần
        }
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
            <div id="deviceVerifyModal" class="modal-overlay">
                <div class="modal-container">
                    <button id="btnCloseModal" class="btn-modal-close" aria-label="Đóng">&times;</button>
                    <!-- Step 1: Initial Notice -->
                    <div id="verifyStep1" class="modal-body-custom">
                        <div class="modal-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="modal-title-custom">Thiết bị chưa xác thực</h5>
                        <p class="modal-subtitle">Tài khoản này đang đăng nhập trên thiết bị khác. Vui lòng xác thực để tiếp tục.</p>
                        <button id="btnStartVerify" class="btn-modal-primary">Xác thực ngay</button>
                    </div>
                    
                    <!-- Step 2: Choose Method -->
                    <div id="verifyStep2" class="modal-body-custom d-none-custom">
                        <h6 class="modal-title-custom">Chọn phương thức xác thực</h6>
                        
                        <div class="d-grid-custom gap-2-custom">
                            <label class="btn-modal-outline radio-option">
                                <input type="radio" name="verifyMethod" value="email" checked>
                                <span class="option-icon"><i class="fas fa-envelope"></i></span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600;">Xác thực qua Email</div>
                                    <small style="color: #6B7280;">Nhận mã OTP qua email</small>
                                </div>
                            </label>
                            
                            <label class="btn-modal-outline radio-option">
                                <input type="radio" name="verifyMethod" value="remote">
                                <span class="option-icon"><i class="fas fa-mobile-alt"></i></span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600;">Phê duyệt từ thiết bị</div>
                                    <small style="color: #6B7280;">Xác nhận từ thiết bị đã đăng nhập</small>
                                </div>
                            </label>
                        </div>
                        
                        <button id="btnConfirmMethod" class="btn-modal-primary" style="margin-top: 16px;">Tiếp tục</button>
                        <button id="btnBackStep1" class="btn-modal-link">← Quay lại</button>
                    </div>
                    
                    <!-- Step 3: Email OTP Form -->
                    <div id="verifyEmailStep" class="modal-body-custom d-none-custom">
                        <h6 class="modal-title-custom"><i class="fas fa-envelope me-2"></i>Xác thực Email</h6>
                        
                        <div id="emailInputStep">
                            <div class="mb-3-custom">
                                <label for="deviceVerifyEmail" class="form-label-custom">Email đã đăng ký</label>
                                <input type="email" id="deviceVerifyEmail" class="form-input-custom" placeholder="example@gmail.com">
                            </div>
                            <button id="btnSendOtp" class="btn-modal-primary">Gửi mã xác thực</button>
                        </div>
                        
                        <div id="otpInputStep" class="d-none-custom">
                            <div class="alert-custom">
                                <i class="fas fa-check-circle"></i> Mã đã gửi đến: <strong id="displayMaskedEmail"></strong>
                            </div>
                            <div class="mb-3-custom">
                                <label for="deviceVerifyOtpCode" class="form-label-custom">Nhập mã OTP (6 số)</label>
                                <input type="text" id="deviceVerifyOtpCode" class="form-input-custom otp-input" maxlength="6" placeholder="000000" autocomplete="one-time-code">
                            </div>
                            <button id="btnVerifyOtp" class="btn-modal-primary" style="background: #48bb78;">Xác nhận</button>
                            <button id="btnResendOtp" class="btn-modal-link">Chưa nhận được mã? Gửi lại (<span id="resendTimerText">2:00</span>)</button>
                        </div>
                        
                        <button id="btnBackToOptions" class="btn-modal-link">← Chọn phương thức khác</button>
                    </div>
                    
                    <!-- Step 4: Remote Approval Waiting -->
                    <div id="verifyRemoteStep" class="modal-body-custom d-none-custom">
                        <div class="spinner-custom"></div>
                        <h6 class="modal-title-custom">Đang chờ phê duyệt...</h6>
                        <p class="modal-subtitle">Vui lòng truy cập vào tài khoản trên thiết bị đã đăng nhập trước đó và phê duyệt yêu cầu này.</p>
                        <div class="alert-custom" style="background: #fffaf0; color: #92400e;">
                            <i class="fas fa-info-circle"></i> Trình duyệt sẽ tự động chuyển hướng sau khi được phê duyệt
                        </div>
                        <button id="btnBackFromRemote" class="btn-modal-link">← Quay lại</button>
                    </div>
                    
                </div>
            </div>
        `;
        
        // Insert modal at the body to avoid being inside the login form
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Add close button event listener
        const closeBtn = document.getElementById('btnCloseModal');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                // Close modal and redirect to login
                const modalEl = document.getElementById('deviceVerifyModal');
                if (modalEl) {
                    modalEl.classList.remove('active');
                }
                // Remove query params and go to login
                window.location.href = '?page=login';
            });
        }
        
        // Show the modal
        const modalEl = document.getElementById('deviceVerifyModal');
        if (modalEl) {
            modalEl.classList.add('active');
        }
    }
    
    // Custom function to hide modal
    function hideModal() {
        const modalEl = document.getElementById('deviceVerifyModal');
        if (modalEl) {
            modalEl.classList.remove('active');
        }
    }
});
