<?php
require_once __DIR__.'/config.php';

class GeoIPService {
    private $db;
    private $cacheEnabled = true;

    public function __construct($db) {
        $this->db = $db;
    }

    public function lookup($ip) {
        // Check cache first
        if ($this->cacheEnabled && $cached = $this->getFromCache($ip)) {
            return $cached;
        }

        // Try DB-IP first
        $result = $this->queryDBIP($ip);
        
        // If DB-IP fails, try fallbacks
        if (empty($result['country_code'])) {
            $fallbacks = json_decode(FALLBACK_SERVICES, true);
            foreach ($fallbacks as $service => $config) {
                $result = $this->queryService($ip, $service, $config);
                if (!empty($result['country_code'])) break;
            }
        }

        // Cache the result
        if ($this->cacheEnabled && !empty($result['country_code'])) {
            $this->saveToCache($ip, $result);
        }

        return $result;
    }

    private function queryDBIP($ip) {
        $url = "https://api.db-ip.com/v2/{$ip}?api_key=".DBIP_API_KEY;
        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            return [
                'ip' => $ip,
                'country_code' => $data['countryCode'] ?? '',
                'region_name' => $data['stateProv'] ?? '',
                'city' => $data['city'] ?? '',
                'isp' => $data['organization'] ?? '',
                'asn' => $data['asNumber'] ?? '',
                'latitude' => $data['latitude'] ?? 0,
                'longitude' => $data['longitude'] ?? 0,
                'accuracy_radius' => $data['accuracyRadius'] ?? 0,
                'ip_service_used' => 'dbip'
            ];
        } catch (Exception $e) {
            error_log("DB-IP lookup failed: " . $e->getMessage());
            return [];
        }
    }

    private function queryService($ip, $service, $config) {
        $url = str_replace('{ip}', $ip, $config['endpoint']).$config['api_key'];
        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            $result = [
                'ip' => $ip,
                'ip_service_used' => $service
            ];
            
            switch($service) {
                case 'ipinfo':
                    $loc = explode(',', $data['loc'] ?? '0,0');
                    $result['country_code'] = $data['country'] ?? '';
                    $result['region_name'] = $data['region'] ?? '';
                    $result['city'] = $data['city'] ?? '';
                    $result['isp'] = $data['org'] ?? '';
                    $result['asn'] = $data['asn'] ?? '';
                    $result['latitude'] = $loc[0] ?? 0;
                    $result['longitude'] = $loc[1] ?? 0;
                    $result['accuracy_radius'] = 0;
                    break;
                    
                case 'ip2location':
                    $result['country_code'] = $data['country_code'] ?? '';
                    $result['region_name'] = $data['region_name'] ?? '';
                    $result['city'] = $data['city_name'] ?? '';
                    $result['isp'] = $data['isp'] ?? '';
                    $result['asn'] = $data['as'] ?? '';
                    $result['latitude'] = $data['latitude'] ?? 0;
                    $result['longitude'] = $data['longitude'] ?? 0;
                    $result['accuracy_radius'] = $data['accuracy_radius'] ?? 0;
                    break;
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("{$service} lookup failed: " . $e->getMessage());
            return [];
        }
    }

    private function getFromCache($ip) {
        $stmt = $this->db->prepare("SELECT * FROM ip_lookup_cache WHERE ip = ?");
        $stmt->bindValue(1, $ip, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        
        if ($result && strtotime($result['updated_at']) > time() - DBIP_CACHE_TTL) {
            return $result;
        }
        return false;
    }

    private function saveToCache($ip, $data) {
        $stmt = $this->db->prepare("
            INSERT OR REPLACE INTO ip_lookup_cache 
            (ip, country_code, region_name, city, isp, asn, latitude, longitude, accuracy_radius) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bindValue(1, $ip);
        $stmt->bindValue(2, $data['country_code']);
        $stmt->bindValue(3, $data['region_name']);
        $stmt->bindValue(4, $data['city']);
        $stmt->bindValue(5, $data['isp']);
        $stmt->bindValue(6, $data['asn']);
        $stmt->bindValue(7, $data['latitude']);
        $stmt->bindValue(8, $data['longitude']);
        $stmt->bindValue(9, $data['accuracy_radius']);
        $stmt->execute();
    }
}