/**
 * Affiliate Finance Module JavaScript
 * Xử lý logic cho Ví ảo, Rút tiền, Commission
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
        const typeFilter = document.getElementById('transactionTypeFilter');
        const statusFilter = document.getElementById('transactionStatusFilter');
        
        const params = new URLSearchParams();
        if (typeFilter && typeFilter.value !== 'all') {
            params.append('type', typeFilter.value);
        }
        if (statusFilter && statusFilter.value !== 'all') {
            params.append('status', statusFilter.value);
        }
        params.append('export', '1');
        
        // Redirect to export endpoint
        window.location.href = '/api/affiliate/transactions/export?' + params.toString();
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
            
            // Get withdrawal limits from DOM data attributes or use defaults
            const minAmount = parseInt(document.getElementById('withdrawalAmount')?.dataset?.minAmount) || 100000;
            const maxAmount = parseInt(document.getElementById('withdrawalAmount')?.dataset?.maxAmount) || 10000000;
            
            if (amount < minAmount) {
                showError('Số tiền rút tối thiểu là ' + formatNumber(minAmount) + ' đ');
                return;
            }
            
            if (amount > maxAmount) {
                showError('Số tiền rút tối đa là ' + formatNumber(maxAmount) + ' đ');
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
            
            // Make actual API call
            fetch('/api/affiliate/withdraw', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    bank_account: bankAccount,
                    amount: amount,
                    note: note
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    // Show success
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
                } else {
                    showError(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Lỗi kết nối. Vui lòng thử lại.');
                console.error('Withdrawal error:', error);
            });
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
    // Commission & Withdrawal - Real API Integration
    // ===================================
    
    // Order Amount Input - Calculate Commission (with real rate from server)
    const orderAmount = document.getElementById('orderAmount');
    if (orderAmount) {
        // Get commission rate from data attribute or use default
        const commissionRate = parseFloat(orderAmount.dataset.commissionRate) || 0.10;
        
        orderAmount.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = value;
            
            const amount = parseInt(value) || 0;
            const commission = amount * commissionRate;
            
            const commissionPreview = document.getElementById('commissionPreview');
            if (commissionPreview) {
                commissionPreview.textContent = formatNumber(commission) + ' đ';
            }
        });
        
        // Trigger on load
        orderAmount.dispatchEvent(new Event('input'));
    }
    
    // Withdrawal Select - Show Preview (from admin panel)
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
    
    // Process Commission from Order (Admin function)
    window.processCommission = function(orderId, amount, type) {
        showLoading();
        
        fetch('/api/affiliate/commission/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                amount: amount,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAlert('Xử lý hoa hồng thành công!', 'success');
                location.reload();
            } else {
                showAlert(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
            console.error('Commission processing error:', error);
        });
    };
    
    // Approve Withdrawal (Admin function)
    window.approveWithdrawal = function(withdrawalId) {
        showLoading();
        
        fetch('/api/affiliate/withdraw/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                withdrawal_id: withdrawalId
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showAlert('Duyệt rút tiền thành công!', 'success');
                location.reload();
            } else {
                showAlert(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
            console.error('Withdrawal approval error:', error);
        });
    };
    
    // Refresh Wallet Status
    window.refreshWalletStatus = function() {
        showLoading();
        
        fetch('/api/affiliate/wallet/status')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                const balanceEl = document.getElementById('currentBalance');
                const frozenEl = document.getElementById('currentFrozen');
                const earnedEl = document.getElementById('currentEarned');
                
                if (balanceEl && data.balance !== undefined) {
                    balanceEl.textContent = formatNumber(data.balance) + ' đ';
                }
                if (frozenEl && data.frozen !== undefined) {
                    frozenEl.textContent = formatNumber(data.frozen) + ' đ';
                }
                if (earnedEl && data.earned !== undefined) {
                    earnedEl.textContent = formatNumber(data.earned) + ' đ';
                }
                showAlert('Đã làm mới trạng thái ví', 'info');
            } else {
                showAlert('Không thể làm mới trạng thái', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Lỗi kết nối', 'error');
            console.error('Refresh error:', error);
        });
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
