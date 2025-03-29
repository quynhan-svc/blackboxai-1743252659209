<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Default report parameters
$dateRange = $_GET['range'] ?? 'today';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;

// Calculate date conditions
$dateConditions = [
    'today' => "DATE(timestamp) = DATE('now')",
    'yesterday' => "DATE(timestamp) = DATE('now', '-1 day')",
    'week' => "timestamp > DATETIME('now', '-7 days')",
    'month' => "timestamp > DATETIME('now', '-1 month')"
];

// Get report data
$where = $dateConditions[$dateRange] ?? $dateConditions['today'];
$offset = ($page - 1) * $perPage;

$total = $db->querySingle("SELECT COUNT(*) FROM clicks WHERE $where");
$results = $db->query("SELECT * FROM clicks WHERE $where ORDER BY timestamp DESC LIMIT $perPage OFFSET $offset");
$reportData = [];

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $reportData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="header-bar">
            <h1><i class="fas fa-chart-bar"></i> Click Reports</h1>
            <div class="report-actions">
                <a href="?export=csv&range=<?= $dateRange ?>" class="btn-export">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
        </div>

        <div class="report-filters">
            <div class="filter-group">
                <label>Date Range:</label>
                <div class="filter-options">
                    <a href="?range=today" class="<?= $dateRange === 'today' ? 'active' : '' ?>">Today</a>
                    <a href="?range=yesterday" class="<?= $dateRange === 'yesterday' ? 'active' : '' ?>">Yesterday</a>
                    <a href="?range=week" class="<?= $dateRange === 'week' ? 'active' : '' ?>">Last 7 Days</a>
                    <a href="?range=month" class="<?= $dateRange === 'month' ? 'active' : '' ?>">Last 30 Days</a>
                </div>
            </div>
        </div>

        <div class="report-summary">
            <div class="summary-card">
                <h3>Total Clicks</h3>
                <p><?= number_format($total) ?></p>
            </div>
            <div class="summary-card">
                <h3>Unique IPs</h3>
                <p><?= number_format($db->querySingle("SELECT COUNT(DISTINCT ip) FROM clicks WHERE $where")) ?></p>
            </div>
            <div class="summary-card">
                <h3>Suspicious</h3>
                <p><?= number_format($db->querySingle("SELECT COUNT(*) FROM clicks WHERE $where AND (is_vpn = 1 OR is_proxy = 1)")) ?></p>
            </div>
        </div>

        <div class="report-table-container">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>IP Address</th>
                        <th>Location</th>
                        <th>Network</th>
                        <th>Destination</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $row): ?>
                    <tr>
                        <td><?= date('M j H:i', strtotime($row['timestamp'])) ?></td>
                        <td>
                            <?= htmlspecialchars($row['ip']) ?>
                            <?php if ($row['is_vpn']): ?>
                                <span class="vpn-badge">VPN</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['city']): ?>
                                <?= htmlspecialchars($row['city']) ?>, 
                            <?php endif; ?>
                            <?= htmlspecialchars($row['country_code'] ?? '') ?>
                        </td>
                        <td>
                            <div class="isp-info"><?= htmlspecialchars($row['isp'] ?? 'Unknown') ?></div>
                            <div class="asn-info">AS<?= htmlspecialchars($row['asn'] ?? '') ?></div>
                        </td>
                        <td class="url-cell"><?= htmlspecialchars(parse_url($row['gad_url'], PHP_URL_HOST)) ?></td>
                        <td>
                            <?php if ($row['is_duplicate']): ?>
                                <span class="badge-warning">Duplicate</span>
                            <?php else: ?>
                                <span class="badge-success">Valid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?range=<?= $dateRange ?>&page=<?= $page - 1 ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <span class="page-info">Page <?= $page ?> of <?= ceil($total / $perPage) ?></span>

                <?php if ($page * $perPage < $total): ?>
                    <a href="?range=<?= $dateRange ?>&page=<?= $page + 1 ?>" class="page-link">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>