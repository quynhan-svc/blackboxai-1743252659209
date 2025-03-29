-- Database schema for Google Ads Tracker
CREATE TABLE clicks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip VARCHAR(45) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    region VARCHAR(100),
    useragent TEXT,
    gad_url TEXT,
    referrer_url TEXT,
    is_duplicate BOOLEAN DEFAULT 0,
    is_suspicious BOOLEAN DEFAULT 0
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_md5 VARCHAR(32) NOT NULL,
    role ENUM('admin','report_viewer') NOT NULL DEFAULT 'report_viewer',
    last_login DATETIME
);

CREATE TABLE settings (
    mail_host VARCHAR(100),
    mail_port INT DEFAULT 587,
    mail_encryption VARCHAR(10) DEFAULT 'tls',
    mail_username VARCHAR(100),
    mail_password VARCHAR(100),
    site_title VARCHAR(100),
    admin_email VARCHAR(100),
    alert_threshold INT DEFAULT 5
);

CREATE TABLE report_permissions (
    user_id INTEGER PRIMARY KEY REFERENCES users(id),
    can_view_daily BOOLEAN DEFAULT 1,
    can_view_3day BOOLEAN DEFAULT 1,
    can_export BOOLEAN DEFAULT 1
);

CREATE TABLE security_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER REFERENCES users(id),
    event TEXT NOT NULL,
    ip VARCHAR(45) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Add geoip columns to clicks table
ALTER TABLE clicks ADD COLUMN country_code VARCHAR(2);
ALTER TABLE clicks ADD COLUMN region_name VARCHAR(100);
ALTER TABLE clicks ADD COLUMN city VARCHAR(100);
ALTER TABLE clicks ADD COLUMN isp VARCHAR(100);
ALTER TABLE clicks ADD COLUMN asn VARCHAR(50);
ALTER TABLE clicks ADD COLUMN latitude REAL;
ALTER TABLE clicks ADD COLUMN longitude REAL;
ALTER TABLE clicks ADD COLUMN accuracy_radius INT;
ALTER TABLE clicks ADD COLUMN ip_service_used VARCHAR(20);

-- Create IP lookup cache table
CREATE TABLE ip_lookup_cache (
    ip VARCHAR(45) PRIMARY KEY,
    country_code VARCHAR(2),
    region_name VARCHAR(100),
    city VARCHAR(100),
    isp VARCHAR(100),
    asn VARCHAR(50),
    latitude REAL,
    longitude REAL,
    accuracy_radius INT,
    is_vpn BOOLEAN DEFAULT 0,
    is_proxy BOOLEAN DEFAULT 0,
    is_tor BOOLEAN DEFAULT 0,
    threat_score INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vpn_providers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    asn VARCHAR(50),
    ip_range_start VARCHAR(45),
    ip_range_end VARCHAR(45)
);

-- Insert known VPN providers
INSERT INTO vpn_providers (name, asn) VALUES
('NordVPN', 'AS57172'),
('ExpressVPN', 'AS397654'),
('Surfshark', 'AS212238'),
('CyberGhost', 'AS49981'),
('Private Internet Access', 'AS60626');
