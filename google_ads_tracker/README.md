# Google Ads Tracker - Hướng dẫn sử dụng

## Thông tin chung
- Phiên bản: 2.0
- Ngày phát hành: 29/03/2025
- Tác giả: BlackboxAI

## Các tính năng chính
1. Theo dõi click từ Google Ads
2. Phát hiện VPN/Proxy
3. Báo cáo chi tiết theo địa lý
4. Xuất dữ liệu CSV

## URL quan trọng
- Trang chủ: `http://localhost:8000`
- Bảng điều khiển: `http://localhost:8000/admin/dashboard.php`
- Báo cáo: `http://localhost:8000/admin/reports.php`
- Cài đặt: `http://localhost:8000/admin/settings.php`
- Đăng nhập: `http://localhost:8000/admin/login.php`

## Yêu cầu hệ thống
- PHP 8.0+
- SQLite3
- Web server (Apache/Nginx)

## Hướng dẫn cài đặt
1. Giải nén file zip vào thư mục web
2. Cấp quyền ghi cho thư mục `database/`
3. Truy cập `http://yourdomain.com/install/` để cài đặt

## Cấu hình
- File cấu hình chính: `includes/config.php`
- Cấu hình địa lý: `includes/geoip.php`
- Cấu hình giao diện: `assets/css/admin.css`

## Hỗ trợ
Liên hệ hỗ trợ qua email: hautran@solar-nhatrang.com