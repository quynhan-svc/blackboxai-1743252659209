<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/functions.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['gad_url']) || !filter_var($data['gad_url'], FILTER_VALIDATE_URL)) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid URL']));
}

// Get client IP with Cloudflare support
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} else {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    // Handle multiple IPs in X-Forwarded-For
    if (strpos($ip, ',') !== false) {
        $ips = explode(',', $ip);
        $ip = trim($ips[0]);
    }
}

// Get enhanced IP info
require_once __DIR__.'/../includes/geoip.php';
$geoService = new GeoIPService($db);
$ipInfo = $geoService->lookup($ip);

// Prepare click data with enhanced geo info
$click = [
    'ip' => $ip,
    'country_code' => $ipInfo['country_code'] ?? '',
    'region_name' => $ipInfo['region_name'] ?? getVietnamRegion($ip),
    'city' => $ipInfo['city'] ?? '',
    'isp' => $ipInfo['isp'] ?? '',
    'asn' => $ipInfo['asn'] ?? '',
    'latitude' => $ipInfo['latitude'] ?? 0,
    'longitude' => $ipInfo['longitude'] ?? 0,
    'accuracy_radius' => $ipInfo['accuracy_radius'] ?? 0,
    'ip_service_used' => $ipInfo['ip_service_used'] ?? 'unknown',
    'is_vpn' => $ipInfo['is_vpn'] ?? 0,
    'is_proxy' => $ipInfo['is_proxy'] ?? 0,
    'is_tor' => $ipInfo['is_tor'] ?? 0,
    'threat_score' => $ipInfo['threat_score'] ?? 0,
    'useragent' => $data['useragent'],
    'gad_url' => $data['gad_url'],
    'referrer_url' => $data['referrer']
];

// Check for duplicates
$stmt = $db->prepare("SELECT COUNT(*) FROM clicks 
                     WHERE ip = :ip AND gad_url = :url 
                     AND timestamp > datetime('now', '-1 hour')");
$stmt->bindValue(':ip', $click['ip'], SQLITE3_TEXT);
$stmt->bindValue(':url', $click['gad_url'], SQLITE3_TEXT);
$duplicateCount = $stmt->execute()->fetchArray()[0];

if ($duplicateCount > 0) {
    $click['is_duplicate'] = 1;
}

// Save to database with enhanced fields
$stmt = $db->prepare("INSERT INTO clicks 
    (ip, country_code, region_name, city, isp, asn, latitude, longitude, 
     accuracy_radius, ip_service_used, is_vpn, is_proxy, is_tor, threat_score,
     useragent, gad_url, referrer_url, is_duplicate) 
    VALUES (:ip, :country, :region, :city, :isp, :asn, :lat, :lng, 
            :accuracy, :service, :is_vpn, :is_proxy, :is_tor, :threat_score,
            :ua, :url, :ref, :dup)");
$stmt->bindValue(':ip', $click['ip'], SQLITE3_TEXT);
$stmt->bindValue(':country', $click['country_code'], SQLITE3_TEXT);
$stmt->bindValue(':region', $click['region_name'], SQLITE3_TEXT);
$stmt->bindValue(':city', $click['city'], SQLITE3_TEXT);
$stmt->bindValue(':isp', $click['isp'], SQLITE3_TEXT);
$stmt->bindValue(':asn', $click['asn'], SQLITE3_TEXT);
$stmt->bindValue(':lat', $click['latitude'], SQLITE3_FLOAT);
$stmt->bindValue(':lng', $click['longitude'], SQLITE3_FLOAT);
$stmt->bindValue(':accuracy', $click['accuracy_radius'], SQLITE3_INTEGER);
$stmt->bindValue(':service', $click['ip_service_used'], SQLITE3_TEXT);
$stmt->bindValue(':is_vpn', $click['is_vpn'], SQLITE3_INTEGER);
$stmt->bindValue(':is_proxy', $click['is_proxy'], SQLITE3_INTEGER);
$stmt->bindValue(':is_tor', $click['is_tor'], SQLITE3_INTEGER);
$stmt->bindValue(':threat_score', $click['threat_score'], SQLITE3_INTEGER);
$stmt->bindValue(':ua', $click['useragent'], SQLITE3_TEXT);
$stmt->bindValue(':url', $click['gad_url'], SQLITE3_TEXT);
$stmt->bindValue(':ref', $click['referrer_url'], SQLITE3_TEXT);
$stmt->bindValue(':dup', $click['is_duplicate'] ?? 0, SQLITE3_INTEGER);
$stmt->execute();

// Log successful tracking
error_log("Tracked click from IP: {$ip} using service: {$click['ip_service_used']}");

echo json_encode(['status' => 'success']);