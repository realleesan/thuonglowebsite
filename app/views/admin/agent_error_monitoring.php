<?php
/**
 * Agent Error Monitoring Dashboard
 * Requirements: 5.4
 */

require_once __DIR__ . '/../../services/AgentErrorHandler.php';

$errorHandler = new AgentErrorHandler();
$errorStats = $errorHandler->getErrorStats(24); // Last 24 hours
?>

<div class="admin-content">
    <div class="page-header">
        <h1>Agent System Error Monitoring</h1>
        <p class="page-description">Monitor errors and system health for agent registration system</p>
    </div>

    <!-- Error Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon error">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?= $errorStats['total_errors'] ?? 0 ?></h3>
                <p>Total Errors (24h)</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?= count($errorStats['recent_errors'] ?? []) ?></h3>
                <p>Recent Errors</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-content">
                <h3><?= count($errorStats['by_type'] ?? []) ?></h3>
                <p>Error Types</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?= ($errorStats['total_errors'] ?? 0) < 10 ? 'Good' : 'Alert' ?></h3>
                <p>System Health</p>
            </div>
        </div>
    </div>

    <!-- Error Types Chart -->
    <?php if (!empty($errorStats['by_type'])): ?>
    <div class="chart-section">
        <div class="chart-widget">
            <div class="widget-header">
                <h3>Error Types Distribution</h3>
            </div>
            <div class="widget-content">
                <div class="error-types-chart">
                    <?php foreach ($errorStats['by_type'] as $type => $count): ?>
                    <div class="error-type-item">
                        <div class="error-type-label"><?= htmlspecialchars($type) ?></div>
                        <div class="error-type-bar">
                            <div class="error-type-fill" style="width: <?= ($count / max($errorStats['by_type'])) * 100 ?>%"></div>
                        </div>
                        <div class="error-type-count"><?= $count ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Errors -->
    <div class="recent-errors-section">
        <div class="section-header">
            <h2>Recent Errors</h2>
            <button class="btn btn-secondary" onclick="refreshErrorData()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <?php if (empty($errorStats['recent_errors'])): ?>
        <div class="no-errors-message">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>No Recent Errors</h3>
            <p>The agent registration system is running smoothly with no errors in the last 24 hours.</p>
        </div>
        <?php else: ?>
        <div class="errors-table-container">
            <table class="errors-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($errorStats['recent_errors'] as $error): ?>
                    <tr class="error-row error-<?= htmlspecialchars($error['type']) ?>">
                        <td class="error-timestamp"><?= htmlspecialchars($error['timestamp']) ?></td>
                        <td class="error-type">
                            <span class="type-badge type-<?= htmlspecialchars($error['type']) ?>">
                                <?= htmlspecialchars($error['type']) ?>
                            </span>
                        </td>
                        <td class="error-message"><?= htmlspecialchars($error['message']) ?></td>
                        <td class="error-actions">
                            <button class="btn btn-sm btn-info" onclick="showErrorDetails('<?= htmlspecialchars(json_encode($error)) ?>')">
                                Details
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- System Health Recommendations -->
    <div class="recommendations-section">
        <div class="section-header">
            <h2>System Health Recommendations</h2>
        </div>

        <div class="recommendations-grid">
            <?php
            $totalErrors = $errorStats['total_errors'] ?? 0;
            $recommendations = [];

            if ($totalErrors > 50) {
                $recommendations[] = [
                    'type' => 'critical',
                    'title' => 'High Error Rate',
                    'message' => 'System is experiencing high error rates. Immediate investigation required.',
                    'action' => 'Check system logs and database connectivity'
                ];
            } elseif ($totalErrors > 20) {
                $recommendations[] = [
                    'type' => 'warning',
                    'title' => 'Moderate Error Rate',
                    'message' => 'System has moderate error rates. Monitor closely.',
                    'action' => 'Review error patterns and optimize code'
                ];
            } else {
                $recommendations[] = [
                    'type' => 'success',
                    'title' => 'System Healthy',
                    'message' => 'Agent registration system is operating normally.',
                    'action' => 'Continue regular monitoring'
                ];
            }

            // Check for specific error types
            if (isset($errorStats['by_type']['agent_spam_prevention']) && $errorStats['by_type']['agent_spam_prevention'] > 10) {
                $recommendations[] = [
                    'type' => 'warning',
                    'title' => 'High Spam Activity',
                    'message' => 'Detected high spam prevention triggers.',
                    'action' => 'Review spam prevention rules and IP blocking'
                ];
            }

            if (isset($errorStats['by_type']['agent_database_error']) && $errorStats['by_type']['agent_database_error'] > 5) {
                $recommendations[] = [
                    'type' => 'critical',
                    'title' => 'Database Issues',
                    'message' => 'Multiple database errors detected.',
                    'action' => 'Check database connectivity and performance'
                ];
            }
            ?>

            <?php foreach ($recommendations as $rec): ?>
            <div class="recommendation-card recommendation-<?= $rec['type'] ?>">
                <div class="recommendation-icon">
                    <?php if ($rec['type'] === 'critical'): ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php elseif ($rec['type'] === 'warning'): ?>
                        <i class="fas fa-exclamation-triangle"></i>
                    <?php else: ?>
                        <i class="fas fa-check-circle"></i>
                    <?php endif; ?>
                </div>
                <div class="recommendation-content">
                    <h4><?= htmlspecialchars($rec['title']) ?></h4>
                    <p><?= htmlspecialchars($rec['message']) ?></p>
                    <small><strong>Action:</strong> <?= htmlspecialchars($rec['action']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Error Details Modal -->
<div id="errorDetailsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Error Details</h3>
            <button type="button" class="close-modal" onclick="closeErrorModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="errorDetailsContent"></div>
        </div>
    </div>
</div>

<style>
.admin-content {
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-icon.error { background-color: #fee2e2; color: #dc2626; }
.stat-icon.warning { background-color: #fef3c7; color: #d97706; }
.stat-icon.info { background-color: #dbeafe; color: #2563eb; }
.stat-icon.success { background-color: #dcfce7; color: #16a34a; }

.stat-content h3 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
}

.stat-content p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.chart-section {
    margin-bottom: 30px;
}

.chart-widget {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.widget-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.widget-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
}

.widget-content {
    padding: 24px;
}

.error-types-chart {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.error-type-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.error-type-label {
    min-width: 150px;
    font-size: 14px;
    color: #374151;
}

.error-type-bar {
    flex: 1;
    height: 20px;
    background-color: #f3f4f6;
    border-radius: 10px;
    overflow: hidden;
}

.error-type-fill {
    height: 100%;
    background-color: #3b82f6;
    transition: width 0.3s ease;
}

.error-type-count {
    min-width: 30px;
    text-align: right;
    font-weight: 600;
    color: #1f2937;
}

.recent-errors-section {
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0;
    color: #1f2937;
    font-size: 20px;
    font-weight: 600;
}

.no-errors-message {
    background: white;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.success-icon {
    font-size: 48px;
    color: #16a34a;
    margin-bottom: 16px;
}

.no-errors-message h3 {
    margin: 0 0 8px 0;
    color: #1f2937;
}

.no-errors-message p {
    margin: 0;
    color: #6b7280;
}

.errors-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.errors-table {
    width: 100%;
    border-collapse: collapse;
}

.errors-table th,
.errors-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.errors-table th {
    background-color: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.type-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.type-agent_registration_error { background-color: #fee2e2; color: #dc2626; }
.type-agent_email_error { background-color: #fef3c7; color: #d97706; }
.type-agent_database_error { background-color: #fee2e2; color: #dc2626; }
.type-agent_spam_prevention { background-color: #fef3c7; color: #d97706; }
.type-agent_validation_error { background-color: #dbeafe; color: #2563eb; }

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.recommendation-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    gap: 16px;
    border-left: 4px solid;
}

.recommendation-critical { border-left-color: #dc2626; }
.recommendation-warning { border-left-color: #d97706; }
.recommendation-success { border-left-color: #16a34a; }

.recommendation-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.recommendation-critical .recommendation-icon { color: #dc2626; }
.recommendation-warning .recommendation-icon { color: #d97706; }
.recommendation-success .recommendation-icon { color: #16a34a; }

.recommendation-content h4 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
}

.recommendation-content p {
    margin: 0 0 8px 0;
    color: #6b7280;
    font-size: 14px;
}

.recommendation-content small {
    color: #374151;
    font-size: 12px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.btn-info {
    background-color: #3b82f6;
    color: white;
}

.btn-info:hover {
    background-color: #2563eb;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: #6b7280;
    cursor: pointer;
}

.modal-body {
    padding: 24px;
}
</style>

<script>
function refreshErrorData() {
    location.reload();
}

function showErrorDetails(errorData) {
    const error = JSON.parse(errorData);
    const content = document.getElementById('errorDetailsContent');
    
    content.innerHTML = `
        <div class="error-detail">
            <h4>Error Information</h4>
            <p><strong>Type:</strong> ${error.type}</p>
            <p><strong>Timestamp:</strong> ${error.timestamp}</p>
            <p><strong>Message:</strong> ${error.message}</p>
        </div>
    `;
    
    document.getElementById('errorDetailsModal').style.display = 'flex';
}

function closeErrorModal() {
    document.getElementById('errorDetailsModal').style.display = 'none';
}

// Auto-refresh every 5 minutes
setInterval(refreshErrorData, 5 * 60 * 1000);
</script>