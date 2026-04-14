<?php
/**
 * Wallet Service
 * Handles wallet and transaction operations for affiliates
 */

require_once __DIR__ . '/../models/AffiliateModel.php';
require_once __DIR__ . '/../models/WalletTransactionModel.php';
require_once __DIR__ . '/../models/WithdrawalRequestModel.php';

class WalletService {
    private AffiliateModel $affiliateModel;
    private WalletTransactionModel $transactionModel;
    private WithdrawalRequestModel $withdrawalModel;
    
    public function __construct() {
        $this->affiliateModel = new AffiliateModel();
        $this->transactionModel = new WalletTransactionModel();
        $this->withdrawalModel = new WithdrawalRequestModel();
    }
    
    /**
     * Record commission from order
     */
    public function recordCommission(int $affiliateId, float $amount, int $orderId, string $description = null): bool {
        try {
            $this->affiliateModel->beginTransaction();
            
            // Get current balance
            $affiliate = $this->affiliateModel->find($affiliateId);
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            
            $balanceBefore = $affiliate['balance'];
            $balanceAfter = $balanceBefore + $amount;
            
            // Credit balance
            $this->affiliateModel->creditBalance($affiliateId, $amount, $description);
            
            // Create transaction record
            $this->transactionModel->createTransaction([
                'affiliate_id' => $affiliateId,
                'type' => WalletTransactionModel::TYPE_COMMISSION,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'order_id' => $orderId,
                'description' => $description ?: "Commission from order #{$orderId}",
                'status' => WalletTransactionModel::STATUS_COMPLETED
            ]);
            
            $this->affiliateModel->commit();
            return true;
            
        } catch (Exception $e) {
            $this->affiliateModel->rollback();
            error_log('Error recording commission: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Debit balance (subtract money from wallet)
     */
    public function debitBalance(int $affiliateId, float $amount, string $description = null): bool {
        try {
            $this->affiliateModel->beginTransaction();
            
            // Get current balance
            $affiliate = $this->affiliateModel->find($affiliateId);
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            
            if ($affiliate['balance'] < $amount) {
                throw new Exception('Insufficient balance');
            }
            
            $balanceBefore = $affiliate['balance'];
            $balanceAfter = $balanceBefore - $amount;
            
            // Debit balance
            $this->affiliateModel->update($affiliateId, [
                'balance' => $balanceAfter,
                'total_commission' => ($affiliate['total_commission'] ?? 0) - $amount
            ]);
            
            // Create transaction record
            $this->transactionModel->createTransaction([
                'affiliate_id' => $affiliateId,
                'type' => WalletTransactionModel::TYPE_REFUND,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description ?: "Balance debit",
                'status' => WalletTransactionModel::STATUS_COMPLETED
            ]);
            
            $this->affiliateModel->commit();
            return true;
            
        } catch (Exception $e) {
            $this->affiliateModel->rollback();
            error_log('Error debiting balance: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Complete withdrawal request
     */
    public function completeWithdrawal(int $withdrawalId): bool {
        try {
            $this->withdrawalModel->beginTransaction();
            
            // Get withdrawal request
            $withdrawal = $this->withdrawalModel->find($withdrawalId);
            if (!$withdrawal) {
                throw new Exception('Withdrawal request not found');
            }
            
            // Check if already completed
            if ($withdrawal['status'] === WithdrawalRequestModel::STATUS_COMPLETED) {
                $this->withdrawalModel->rollback();
                return true; // Already completed
            }
            
            $affiliateId = $withdrawal['affiliate_id'];
            $amount = $withdrawal['net_amount'];
            
            // Get current balance
            $affiliate = $this->affiliateModel->find($affiliateId);
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            
            $balanceBefore = $affiliate['pending_withdrawal'];
            $balanceAfter = $balanceBefore - $amount;
            
            // Complete withdrawal in affiliate model
            $this->affiliateModel->completeWithdrawal($affiliateId, $amount);
            
            // Create transaction record
            $this->transactionModel->createTransaction([
                'affiliate_id' => $affiliateId,
                'type' => WalletTransactionModel::TYPE_WITHDRAWAL,
                'amount' => -$amount, // Negative for withdrawal
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => 'withdrawal_request',
                'reference_id' => $withdrawalId,
                'withdrawal_id' => $withdrawalId,
                'description' => "Withdrawal {$withdrawal['withdraw_code']}",
                'status' => WalletTransactionModel::STATUS_COMPLETED
            ]);
            
            // Update withdrawal status
            $this->withdrawalModel->updateStatus(
                $withdrawalId,
                WithdrawalRequestModel::STATUS_COMPLETED
            );
            
            $this->withdrawalModel->commit();
            return true;
            
        } catch (Exception $e) {
            $this->withdrawalModel->rollback();
            error_log('Error completing withdrawal: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cancel withdrawal request - return money to affiliate balance
     */
    public function cancelWithdrawal(int $withdrawalId, string $adminNote = ''): bool {
        try {
            $this->withdrawalModel->beginTransaction();
            
            // Get withdrawal request
            $withdrawal = $this->withdrawalModel->find($withdrawalId);
            if (!$withdrawal) {
                throw new Exception('Withdrawal request not found');
            }
            
            // Check if already cancelled or completed
            if (in_array($withdrawal['status'], [WithdrawalRequestModel::STATUS_COMPLETED, WithdrawalRequestModel::STATUS_CANCELLED])) {
                $this->withdrawalModel->rollback();
                throw new Exception('Withdrawal request has already been processed');
            }
            
            $affiliateId = $withdrawal['affiliate_id'];
            $amount = $withdrawal['amount']; // Return full amount including fee
            
            // Get current affiliate data
            $affiliate = $this->affiliateModel->find($affiliateId);
            if (!$affiliate) {
                throw new Exception('Affiliate not found');
            }
            
            // Return money from pending_withdrawal to balance
            $this->affiliateModel->unfreezeBalance($affiliateId, $amount);
            
            // Create transaction record for the refund
            $balanceBefore = $affiliate['balance'];
            $balanceAfter = $balanceBefore + $amount;
            
            $this->transactionModel->createTransaction([
                'affiliate_id' => $affiliateId,
                'type' => WalletTransactionModel::TYPE_REFUND,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => 'withdrawal_cancelled',
                'reference_id' => $withdrawalId,
                'withdrawal_id' => $withdrawalId,
                'description' => "Hoàn tiền yêu cầu rút {$withdrawal['withdraw_code']}" . ($adminNote ? " - Lý do: {$adminNote}" : ''),
                'status' => WalletTransactionModel::STATUS_COMPLETED
            ]);
            
            // Update withdrawal status
            $this->withdrawalModel->updateStatus(
                $withdrawalId,
                WithdrawalRequestModel::STATUS_CANCELLED,
                null,
                $adminNote
            );
            
            $this->withdrawalModel->commit();
            return true;
            
        } catch (Exception $e) {
            $this->withdrawalModel->rollback();
            error_log('Error cancelling withdrawal: ' . $e->getMessage());
            throw $e;
        }
    }
}
