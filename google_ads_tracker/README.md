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

## WordPress Integration
The system includes a WordPress plugin for easy integration:
1. Plugin location: `wordpress/google-ads-tracker.php`
2. Features:
   - Automatic script injection in footer
   - REST API endpoint for tracking
   - Data forwarding to main tracking system
3. Installation guide: See `wordpress/INSTALL.md`

## What's New in Version 2.1
- **Cải thiện hệ thống cài đặt**:
  - Tự động tạo thư mục database
  - Thêm dữ liệu mẫu khi cài đặt
  - Kiểm tra trạng thái cài đặt tự động

- **Nâng cấp bảo mật**:
  - Thay thế md5 bằng password_hash
  - Cải thiện xử lý lỗi database
  - Kiểm tra cài đặt trước khi truy cập admin

- **Tích hợp mới**:
  - Hệ thống phát hiện cài đặt chưa hoàn tất
  - Tự động chuyển hướng đến trang cài đặt
  - Ghi nhận thời gian tạo user

## Hỗ trợ
Liên hệ hỗ trợ qua email: hautran@solar-nhatrang.com
