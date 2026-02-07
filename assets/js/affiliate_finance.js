/**
 * Affiliate Finance Module JavaScript
 * Xử lý logic cho Ví ảo, Rút tiền, Webhook simulation
 */

(function() {
    'use strict';

    // ===================================
    // Transaction Filtering
    // ===================================
    window.filterTransactions = function() {
        const typeFilter = document.getElementById('transactionTypeFilter');
        const statusFilter = document.getElementById('transactionStatusFilter');
        const rows = document.querySelectorAll('.transaction-row');
        const emptyState = document.getElementById('emptyState');
        
        if (!typeFilter || !statusFilter || !rows.length) return;
        
        const selectedType = typeFilter.value;
        const selectedStatus = statusFilter.value;
        let visibleCount = 0;
        
        rows.forEach(row => {
            const rowType = row.getAttribute('data-type');
            const rowStatus = row.getAttribute('data-status');
            
            const typeMatch = selectedType === 'all' || rowType === selectedType;
            const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus;
            
            if (typeMatch && statusMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide empty state
        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    };

    window.resetTransactionFilters = function() {
        const typeFilter = document.getElementById('transactionTypeFilter');
        const statusFilter = document.getElementById('transactionStatusFilter');
        
        if (typeFilter) typeFilter.value = 'all';
        if (statusFilter) statusFilter.value = 'all';
        
        filterTransactions();
    };

    window.exportTransactions = function() {
        showAlert('Tính năng xuất Excel đang được phát triển', 'info');
    };

    // ===================================
    // Withdrawal Form
    // ===================================
    
    // Bank Account Selection
    const bankAccountSelect = document.getElementById('bankAccountSelect');
    if (bankAccountSelect) {
        bankAccountSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const bankDetails = document.getElementById('bankDetails');
            
            if (this.value) {
                const bankName = selectedOption.getAttribute('data-bank');
                const accountNumber = selectedOption.getAttribute('data-account');
                const accountHolder = selectedOption.getAttribute('data-holder');
                
                document.getElementById('bankName').textContent = bankName;
                document.getElementById('accountNumber').textContent = accountNumber;
                document.getElementById('accountHolder').textContent = accountHolder;
                
                if (bankDetails) {
                    bankDetails.style.display = 'block';
                }
            } else {
                if (bankDetails) {
                    bankDetails.style.display = 'none';
                }
            }
        });
        
        // Trigger change on page load if default selected
        if (bankAccountSelect.value) {
            bankAccountSelect.dispatchEvent(new Event('change'));
        }
    }

    // Amount Input - Format and Calculate
    const withdrawalAmount = document.getElementById('withdrawalAmount');
    if (withdrawalAmount) {
        withdrawalAmount.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/[^0-9]/g, '');
            
            // Update input with formatted value
            this.value = value;
            
            // Calculate balance after withdrawal
            calculateBalanceAfter(parseInt(value) || 0);
        });
    }

    function calculateBalanceAfter(amount) {
        const availableBalanceEl = document.getElementById('availableBalance');
        const balancePreview = document.getElementById('balancePreview');
        const withdrawAmount = document.getElementById('withdrawAmount');
        const balanceAfter = document.getElementById('balanceAfter');
        
        if (!availableBalanceEl || !balancePreview) return;
        
        const availableBalance = parseInt(availableBalanceEl.getAttribute('data-balance')) || 0;
        const remaining = availableBalance - amount;
        
        if (amount > 0) {
            balancePreview.style.display = 'block';
            
            if (withdrawAmount) {
                withdrawAmount.textContent = '-' + formatNumber(amount) + ' đ';
            }
            
            if (balanceAfter) {
                balanceAfter.textContent = formatNumber(remaining) + ' đ';
                
                // Change color based on remaining balance
                if (remaining < 0) {
                    balanceAfter.style.color = '#EF4444';
                } else if (remaining < 500000) {
                    balanceAfter.style.color = '#F59E0B';
                } else {
                    balanceAfter.style.color = '#10B981';
                }
            }
        } else {
            balancePreview.style.display = 'none';
        }
    }

    window.setAmount = function(amount) {
        const withdrawalAmount = document.getElementById('withdrawalAmount');
        if (withdrawalAmount) {
            withdrawalAmount.value = amount;
            calculateBalanceAfter(amount);
        }
    };

    // Withdrawal Form Submission
    const withdrawalForm = document.getElementById('withdrawalForm');
    if (withdrawalForm) {
        withdrawalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const bankAccount = document.getElementById('bankAccountSelect').value;
            const amount = parseInt(document.getElementById('withdrawalAmount').value) || 0;
            const note = document.getElementById('withdrawalNote').value;
            const availableBalance = parseInt(document.getElementById('availableBalance').getAttribute('data-balance')) || 0;
            const errorMessage = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            
            // Validation
            if (!bankAccount) {
                showError('Vui lòng chọn tài khoản ngân hàng');
                return;
            }
            
            if (amount < 500000) {
                showError('Số tiền rút tối thiểu là 500,000 đ');
                return;
            }
            
            if (amount > 50000000) {
                showError('Số tiền rút tối đa là 50,000,000 đ');
                return;
            }
            
            if (amount > availableBalance) {
                showError('Số dư không đủ để thực hiện giao dịch');
                return;
            }
            
            // Hide error
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
            
            // Show loading
            showLoading();
            
            // Simulate API call
            setTimeout(function() {
                hideLoading();
                
                // Show success message with detailed info
                const successMessage = `
                    <div style="text-align: center;">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #10B981; margin-bottom: 16px;"></i>
                        <h3 style="margin: 0 0 12px 0; color: #111827;">Yêu cầu rút tiền đã được gửi!</h3>
                        <p style="margin: 0 0 8px 0; color: #6B7280;">Số tiền: <strong>${formatNumber(amount)} đ</strong></p>
                        <p style="margin: 0 0 16px 0; color: #6B7280;">Yêu cầu của bạn đã được gửi đến Admin để xử lý.</p>
                        <div style="background: #FEF3C7; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                            <p style="margin: 0; color: #92400E; font-size: 14px;">
                                <i class="fas fa-clock"></i> 
                                Thời gian xử lý: <strong>1-3 ngày làm việc</strong>
                            </p>
                        </div>
                        <p style="margin: 0; color: #6B7280; font-size: 13px;">
                            Bạn có thể xem trạng thái rút tiền tại trang 
                            <a href="?page=affiliate&module=finance" style="color: #356DF1;">Ví của tôi</a>
                        </p>
                    </div>
                `;
                
                // Create custom alert
                const alertDiv = document.createElement('div');
                alertDiv.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 32px;
                    border-radius: 12px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
                    z-index: 10000;
                    max-width: 500px;
                    width: 90%;
                `;
                alertDiv.innerHTML = successMessage + `
                    <button onclick="this.parentElement.remove(); document.getElementById('overlay').remove(); window.location.href='?page=affiliate&module=finance'" 
                            style="width: 100%; padding: 12px; background: #356DF1; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 16px;">
                        Đóng và quay về Ví
                    </button>
                `;
                
                // Create overlay
                const overlay = document.createElement('div');
                overlay.id = 'overlay';
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 9999;
                `;
                
                document.body.appendChild(overlay);
                document.body.appendChild(alertDiv);
            }, 1500);
        });
    }

    function showError(message) {
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        
        if (errorMessage && errorText) {
            errorText.textContent = message;
            errorMessage.style.display = 'flex';
            
            // Scroll to error
            errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // ===================================
    // Webhook Demo
    // ===================================
    
    // Order Amount Input - Calculate Commission
    const orderAmount = document.getElementById('orderAmount');
    if (orderAmount) {
        orderAmount.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = value;
            
            const amount = parseInt(value) || 0;
            const commission = amount * 0.1; // 10% commission
            
            const commissionPreview = document.getElementById('commissionPreview');
            if (commissionPreview) {
                commissionPreview.textContent = formatNumber(commission) + ' đ';
            }
        });
        
        // Trigger on load
        orderAmount.dispatchEvent(new Event('input'));
    }

    // Withdrawal Select - Show Preview
    const withdrawalSelect = document.getElementById('withdrawalSelect');
    if (withdrawalSelect) {
        withdrawalSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const withdrawalPreview = document.getElementById('withdrawalPreview');
            const approveBtn = document.getElementById('approveBtn');
            
            if (this.value) {
                const amount = selectedOption.getAttribute('data-amount');
                const code = selectedOption.getAttribute('data-code');
                
                document.getElementById('previewCode').textContent = code;
                document.getElementById('previewAmount').textContent = formatNumber(amount) + ' đ';
                
                if (withdrawalPreview) {
                    withdrawalPreview.style.display = 'block';
                }
                
                if (approveBtn) {
                    approveBtn.disabled = false;
                }
            } else {
                if (withdrawalPreview) {
                    withdrawalPreview.style.display = 'none';
                }
                
                if (approveBtn) {
                    approveBtn.disabled = true;
                }
            }
        });
    }

    // Simulate Commission
    window.simulateCommission = function() {
        const orderAmount = document.getElementById('orderAmount');
        const orderType = document.getElementById('orderType');
        
        if (!orderAmount || !orderType) return;
        
        const amount = parseInt(orderAmount.value.replace(/[^0-9]/g, '')) || 0;
        const type = orderType.value;
        const commission = amount * 0.1;
        
        if (amount < 100000) {
            showAlert('Số tiền đơn hàng tối thiểu là 100,000 đ', 'warning');
            return;
        }
        
        // Update wallet balance
        const currentBalance = document.getElementById('currentBalance');
        const currentEarned = document.getElementById('currentEarned');
        
        if (currentBalance) {
            const balance = parseInt(currentBalance.textContent.replace(/[^0-9]/g, '')) || 0;
            const newBalance = balance + commission;
            currentBalance.textContent = formatNumber(newBalance) + ' đ';
            
            // Animate
            currentBalance.style.color = '#10B981';
            setTimeout(() => {
                currentBalance.style.color = '';
            }, 2000);
        }
        
        if (currentEarned) {
            const earned = parseInt(currentEarned.textContent.replace(/[^0-9]/g, '')) || 0;
            const newEarned = earned + commission;
            currentEarned.textContent = formatNumber(newEarned) + ' đ';
        }
        
        // Add log
        const typeName = type === 'logistics' ? 'Logistics' : 'Data Subscription';
        addWebhookLog('success', `Nhận hoa hồng ${formatNumber(commission)} đ từ đơn hàng ${typeName} (${formatNumber(amount)} đ)`);
        
        // Show notification
        showAlert(`Nhận hoa hồng thành công: ${formatNumber(commission)} đ`, 'success');
    };

    // Simulate Withdrawal Approval
    window.simulateWithdrawalApproval = function() {
        const withdrawalSelect = document.getElementById('withdrawalSelect');
        
        if (!withdrawalSelect || !withdrawalSelect.value) {
            showAlert('Vui lòng chọn lệnh rút', 'warning');
            return;
        }
        
        const selectedOption = withdrawalSelect.options[withdrawalSelect.selectedIndex];
        const amount = parseInt(selectedOption.getAttribute('data-amount')) || 0;
        const code = selectedOption.getAttribute('data-code');
        
        // Update wallet balance
        const currentBalance = document.getElementById('currentBalance');
        const currentFrozen = document.getElementById('currentFrozen');
        
        if (currentFrozen) {
            const frozen = parseInt(currentFrozen.textContent.replace(/[^0-9]/g, '')) || 0;
            const newFrozen = Math.max(0, frozen - amount);
            currentFrozen.textContent = formatNumber(newFrozen) + ' đ';
            
            // Animate
            currentFrozen.style.color = '#EF4444';
            setTimeout(() => {
                currentFrozen.style.color = '';
            }, 2000);
        }
        
        // Add log
        addWebhookLog('success', `Lệnh rút tiền ${code} đã được duyệt. Số tiền ${formatNumber(amount)} đ đã được chuyển khoản.`);
        
        // Show notification
        showAlert(`Lệnh rút tiền ${code} đã được duyệt thành công!`, 'success');
        
        // Remove from select and reset
        const selectedIndex = withdrawalSelect.selectedIndex;
        withdrawalSelect.remove(selectedIndex);
        
        // Reset select to default
        withdrawalSelect.selectedIndex = 0;
        
        // Hide preview and disable button
        const withdrawalPreview = document.getElementById('withdrawalPreview');
        const approveBtn = document.getElementById('approveBtn');
        
        if (withdrawalPreview) {
            withdrawalPreview.style.display = 'none';
        }
        
        if (approveBtn) {
            approveBtn.disabled = true;
        }
        
        // Check if no more pending withdrawals
        if (withdrawalSelect.options.length <= 1) {
            // Only default option left
            withdrawalSelect.disabled = true;
            if (approveBtn) {
                approveBtn.disabled = true;
                approveBtn.innerHTML = '<i class="fas fa-info-circle"></i><span>Không có lệnh rút nào đang chờ</span>';
            }
        }
    };

    // Refresh Wallet Status
    window.refreshWalletStatus = function() {
        showLoading();
        
        setTimeout(function() {
            hideLoading();
            showAlert('Đã làm mới trạng thái ví', 'info');
        }, 500);
    };

    // Clear Logs
    window.clearLogs = function() {
        const webhookLogs = document.getElementById('webhookLogs');
        if (webhookLogs) {
            webhookLogs.innerHTML = `
                <div class="log-empty">
                    <i class="fas fa-info-circle"></i>
                    <p>Chưa có webhook nào được kích hoạt</p>
                </div>
            `;
        }
    };

    // Add Webhook Log
    function addWebhookLog(type, message) {
        const webhookLogs = document.getElementById('webhookLogs');
        if (!webhookLogs) return;
        
        // Remove empty state
        const emptyState = webhookLogs.querySelector('.log-empty');
        if (emptyState) {
            emptyState.remove();
        }
        
        // Create log entry
        const logEntry = document.createElement('div');
        logEntry.className = 'log-entry' + (type === 'error' ? ' log-error' : '');
        
        const timestamp = new Date().toLocaleString('vi-VN');
        logEntry.innerHTML = `
            <div class="log-timestamp">[${timestamp}]</div>
            <div class="log-message">${message}</div>
        `;
        
        // Add to top
        webhookLogs.insertBefore(logEntry, webhookLogs.firstChild);
        
        // Limit to 10 logs
        const logs = webhookLogs.querySelectorAll('.log-entry');
        if (logs.length > 10) {
            logs[logs.length - 1].remove();
        }
    }

    // ===================================
    // Utility Functions
    // ===================================
    
    function formatNumber(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    }

    // ===================================
    // Initialize
    // ===================================
    
    console.log('Finance Module Initialized');

})();
