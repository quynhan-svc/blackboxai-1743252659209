# Google Ads Tracker - Hướng dẫn sử dụng

## Thông tin chung
- Phiên bản: 2.2
- Ngày phát hành: 29/03/2025
- Tác giả: BlackboxAI

## Các tính năng chính
1. Theo dõi click từ Google Ads
2. Phát hiện VPN/Proxy
3. Báo cáo chi tiết theo địa lý
4. Xuất dữ liệu CSV
5. Quản lý người dùng nâng cao

## What's New in Version 2.2

### Hệ Thống Quản Lý Người Dùng
- **Tính năng quản lý**:
  - Thêm/Xóa/Sửa người dùng
  - Reset mật khẩu
  - Phân quyền Admin/Report Viewer
  - Kiểm tra lần đăng nhập cuối

- **Bảo mật**:
  - Chống tự xóa tài khoản
  - Mật khẩu được hash bằng password_hash
  - Xác thực quyền admin

- **Giao diện**:
  - Modal để chỉnh sửa thông tin
  - Responsive cho mobile
  - Thông báo trạng thái rõ ràng

### Cải Tiến Hệ Thống
- **Cài đặt**:
  - Tự động tạo database
  - Thêm dữ liệu mẫu
  - Kiểm tra phiên bản

- **Bảo mật**:
  - Session timeout
  - Giới hạn số lần đăng nhập
  - CSRF protection

## URL quan trọng
- Trang chủ: `https://<domain>/google_ads_tracker`
- Bảng điều khiển: `https://<domain>/google_ads_tracker/admin/dashboard.php`
- Quản lý người dùng: `https://<domain>/google_ads_tracker/admin/users.php`
- Báo cáo: `https://<domain>/google_ads_tracker/admin/reports.php`
- Cài đặt: `https://<domain>/google_ads_tracker/admin/settings.php`
- Đăng nhập: `https://<domain>/google_ads_tracker/admin/login.php`

Lưu ý: Thay `<domain>` bằng tên miền thực tế của bạn

## Hướng Dẫn Sử Dụng
1. Đăng nhập bằng tài khoản admin (admin/admin123)
2. Truy cập mục "User Management"
3. Thêm/chỉnh sửa người dùng theo nhu cầu
4. Phân quyền phù hợp cho từng user

## Yêu cầu hệ thống
- PHP 8.0+
- SQLite3
- Web server (Apache/Nginx)

## Hỗ trợ
Liên hệ hỗ trợ qua email: hautran@solar-nhatrang.com
