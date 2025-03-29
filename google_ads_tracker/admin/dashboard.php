<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user permissions
$permissions = $db->querySingle("SELECT * FROM report_permissions WHERE user_id = {$_SESSION['user_id']}", true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Google Ads Tracker</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>Google Ads Tracker</h2>
            <ul>
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="users.php">User Management</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php endif; ?>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-bar">
                <h1>Dashboard</h1>
                <div class="theme-toggle">
                    <span class="light-mode"><i class="fas fa-sun"></i></span>
                    <span class="dark-mode"><i class="fas fa-moon"></i></span>
                </div>
            </div>
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Today's Clicks</h3>
                    <p><?= $db->querySingle("SELECT COUNT(*) FROM clicks WHERE DATE(timestamp) = DATE('now')") ?></p>
                </div>
                <div class="stat-card">
                    <h3>Unique IPs</h3>
                    <p><?= $db->querySingle("SELECT COUNT(DISTINCT ip) FROM clicks WHERE DATE(timestamp) = DATE('now')") ?></p>
                </div>
                <div class="stat-card">
                    <h3>Duplicate Clicks</h3>
                    <p><?= $db->querySingle("SELECT COUNT(*) FROM clicks WHERE is_duplicate = 1 AND DATE(timestamp) = DATE('now')") ?></p>
                </div>
            </div>

            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>IP Address</th>
                            <th>Location</th>
                            <th>Network</th>
                            <th>Destination URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $db->query("SELECT * FROM clicks ORDER BY timestamp DESC LIMIT 10");
                        while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($row['timestamp'])) ?></td>
                            <td>
                                <?= $row['ip'] ?>
                                <?php if ($row['is_vpn']): ?>
                                    <span class="vpn-badge">VPN</span>
                                <?php endif; ?>
                                <?php if ($row['is_proxy']): ?>
                                    <span class="proxy-badge">Proxy</span>
                                <?php endif; ?>
                                <?php if ($row['is_tor']): ?>
                                    <span class="tor-badge">Tor</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['city']): ?>
                                    <?= htmlspecialchars($row['city']) ?>, 
                                <?php endif; ?>
                                <?= htmlspecialchars($row['region_name']) ?>
                                <?php if ($row['country_code']): ?>
                                    (<?= htmlspecialchars($row['country_code']) ?>)
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['isp']): ?>
                                    <div class="isp-info"><?= htmlspecialchars($row['isp']) ?></div>
                                <?php endif; ?>
                                <?php if ($row['asn']): ?>
                                    <div class="asn-info"><?= htmlspecialchars($row['asn']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="url-cell"><?= substr(htmlspecialchars($row['gad_url']), 0, 50) ?>...</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="geo-map-container">
                <h2>Geographical Distribution</h2>
                <div id="map" style="height: 400px; background: #f5f7fa;"></div>
                <div class="map-legend">
                    <span class="legend-item"><i class="fas fa-circle" style="color: #3b82f6"></i> Recent Clicks</span>
                    <span class="legend-item"><i class="fas fa-circle" style="color: #10b981"></i> Current Session</span>
                </div>
            </div>

            <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const map = L.map('map').setView([16, 108], 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    <?php
                    $result = $db->query("SELECT * FROM clicks 
                                        WHERE latitude != 0 AND longitude != 0
                                        ORDER BY timestamp DESC LIMIT 50");
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                        L.circleMarker(
                            [<?= $row['latitude'] ?>, <?= $row['longitude'] ?>], 
                            {
                                radius: 5,
                                fillColor: "#3b82f6",
                                color: "#1d4ed8",
                                weight: 1,
                                opacity: 1,
                                fillOpacity: 0.8
                            }
                        ).addTo(map)
                        .bindPopup(`
                            <b>IP:</b> <?= $row['ip'] ?><br>
                            <b>Location:</b> <?= $row['city'] ?>, <?= $row['region_name'] ?><br>
                            <b>ISP:</b> <?= $row['isp'] ?>
                        `);
                    <?php endwhile; ?>
                });
            </script>
        </div>
    </div>
</body>
</html>