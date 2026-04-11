@extends('layouts.admin')

@section('title', 'Cài Đặt Hệ Thống - Admin')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="page-title">
                <i class="fas fa-cog me-3"></i>
                Cài Đặt Hệ Thống
            </h1>
            <p class="page-subtitle">Cấu hình và quản lý các thiết lập hệ thống</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-success" onclick="saveAllSettings()">
                <i class="fas fa-save me-2"></i>
                Lưu Tất Cả
            </button>
        </div>
    </div>
</div>

<!-- Settings Tabs -->
<div class="settings-container">
    <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                <i class="fas fa-sliders-h me-2"></i>
                Tổng Quan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="library-tab" data-bs-toggle="tab" data-bs-target="#library" type="button" role="tab">
                <i class="fas fa-book me-2"></i>
                Thư Viện
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="borrow-tab" data-bs-toggle="tab" data-bs-target="#borrow" type="button" role="tab">
                <i class="fas fa-exchange-alt me-2"></i>
                Mượn Sách
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notification-tab" data-bs-toggle="tab" data-bs-target="#notification" type="button" role="tab">
                <i class="fas fa-bell me-2"></i>
                Thông Báo
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                <i class="fas fa-shield-alt me-2"></i>
                Bảo Mật
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                <i class="fas fa-database me-2"></i>
                Sao Lưu
            </button>
        </li>
    </ul>
    
    <div class="tab-content settings-content" id="settingsTabsContent">
        <!-- General Settings -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Thông Tin Hệ Thống
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Tên Thư Viện</label>
                            <input type="text" class="form-control" name="library_name" value="Thư Viện Đại Học ABC">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Địa Chỉ</label>
                            <input type="text" class="form-control" name="library_address" value="123 Đường ABC, Quận XYZ, TP.HCM">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Số Điện Thoại</label>
                            <input type="text" class="form-control" name="library_phone" value="(028) 1234-5678">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="library_email" value="info@library.edu.vn">
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Mô Tả</label>
                    <textarea class="form-control" name="library_description" rows="3">Thư viện phục vụ sinh viên và giảng viên với hơn 50,000 đầu sách và tài liệu học tập.</textarea>
                </div>
            </div>
            
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-clock me-2"></i>
                    Giờ Làm Việc
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Giờ Mở Cửa</label>
                            <input type="time" class="form-control" name="opening_time" value="07:00">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Giờ Đóng Cửa</label>
                            <input type="time" class="form-control" name="closing_time" value="22:00">
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Ngày Nghỉ</label>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="closed_days[]" value="sunday" id="sunday">
                                <label class="form-check-label" for="sunday">Chủ Nhật</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="closed_days[]" value="saturday" id="saturday">
                                <label class="form-check-label" for="saturday">Thứ Bảy</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Library Settings -->
        <div class="tab-pane fade" id="library" role="tabpanel">
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-book-open me-2"></i>
                    Cài Đặt Sách
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Số Ngày Mượn Tối Đa</label>
                            <input type="number" class="form-control" name="max_borrow_days" value="14" min="1" max="365">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Số Sách Mượn Tối Đa</label>
                            <input type="number" class="form-control" name="max_borrow_books" value="5" min="1" max="20">
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Cho Phép Đặt Trước</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_reservations" id="allow_reservations" checked>
                        <label class="form-check-label" for="allow_reservations">
                            Cho phép độc giả đặt trước sách
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Phí Phạt
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Phí Phạt Mỗi Ngày (VNĐ)</label>
                            <input type="number" class="form-control" name="fine_per_day" value="5000" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Phí Phạt Tối Đa (VNĐ)</label>
                            <input type="number" class="form-control" name="max_fine" value="100000" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Miễn Phí Phạt</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_fine_waiver" id="allow_fine_waiver" checked>
                        <label class="form-check-label" for="allow_fine_waiver">
                            Cho phép miễn phí phạt trong trường hợp đặc biệt
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Borrow Settings -->
        <div class="tab-pane fade" id="borrow" role="tabpanel">
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-hand-holding me-2"></i>
                    Quy Trình Mượn Sách
                </h4>
                
                <div class="setting-item">
                    <label class="form-label">Yêu Cầu Xác Thực</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="require_verification" id="require_verification" checked>
                        <label class="form-check-label" for="require_verification">
                            Yêu cầu xác thực thông tin độc giả khi mượn sách
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Tự Động Phê Duyệt</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_approve" id="auto_approve">
                        <label class="form-check-label" for="auto_approve">
                            Tự động phê duyệt yêu cầu mượn sách
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Cho Phép Mượn Online</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_online_borrow" id="allow_online_borrow" checked>
                        <label class="form-check-label" for="allow_online_borrow">
                            Cho phép độc giả mượn sách trực tuyến
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-calendar-check me-2"></i>
                    Đặt Trước
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Số Ngày Đặt Trước Tối Đa</label>
                            <input type="number" class="form-control" name="max_reservation_days" value="30" min="1" max="365">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Thời Gian Giữ Sách (Giờ)</label>
                            <input type="number" class="form-control" name="hold_duration_hours" value="24" min="1" max="168">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notification Settings -->
        <div class="tab-pane fade" id="notification" role="tabpanel">
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-bell me-2"></i>
                    Thông Báo Tự Động
                </h4>
                
                <div class="setting-item">
                    <label class="form-label">Thông Báo Sắp Hết Hạn</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="notify_due_soon" id="notify_due_soon" checked>
                        <label class="form-check-label" for="notify_due_soon">
                            Gửi thông báo khi sách sắp hết hạn
                        </label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Số Ngày Nhắc Nhở Trước</label>
                            <input type="number" class="form-control" name="reminder_days_before" value="3" min="1" max="30">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Thông Báo Quá Hạn</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="notify_overdue" id="notify_overdue" checked>
                                <label class="form-check-label" for="notify_overdue">
                                    Gửi thông báo khi sách quá hạn
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Email Thông Báo</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="email_notifications" id="email_notifications" checked>
                        <label class="form-check-label" for="email_notifications">
                            Gửi thông báo qua email
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">SMS Thông Báo</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="sms_notifications" id="sms_notifications">
                        <label class="form-check-label" for="sms_notifications">
                            Gửi thông báo qua SMS
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Settings -->
        <div class="tab-pane fade" id="security" role="tabpanel">
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-lock me-2"></i>
                    Bảo Mật Đăng Nhập
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Độ Dài Mật Khẩu Tối Thiểu</label>
                            <input type="number" class="form-control" name="min_password_length" value="8" min="6" max="32">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Thời Gian Phiên (Phút)</label>
                            <input type="number" class="form-control" name="session_timeout" value="120" min="15" max="1440">
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Yêu Cầu Mật Khẩu Phức Tạp</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="require_complex_password" id="require_complex_password" checked>
                        <label class="form-check-label" for="require_complex_password">
                            Yêu cầu mật khẩu có chữ hoa, chữ thường, số và ký tự đặc biệt
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Khóa Tài Khoản Sau Thất Bại</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="lock_after_failed_attempts" id="lock_after_failed_attempts" checked>
                        <label class="form-check-label" for="lock_after_failed_attempts">
                            Khóa tài khoản sau 5 lần đăng nhập thất bại
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-shield-alt me-2"></i>
                    Phân Quyền
                </h4>
                
                <div class="setting-item">
                    <label class="form-label">Kiểm Tra Phân Quyền</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enable_permission_check" id="enable_permission_check" checked>
                        <label class="form-check-label" for="enable_permission_check">
                            Bật kiểm tra phân quyền cho tất cả các chức năng
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Ghi Log Hoạt Động</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="log_user_activities" id="log_user_activities" checked>
                        <label class="form-check-label" for="log_user_activities">
                            Ghi lại tất cả hoạt động của người dùng
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Backup Settings -->
        <div class="tab-pane fade" id="backup" role="tabpanel">
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-database me-2"></i>
                    Sao Lưu Tự Động
                </h4>
                
                <div class="setting-item">
                    <label class="form-label">Sao Lưu Tự Động</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_backup" id="auto_backup" checked>
                        <label class="form-check-label" for="auto_backup">
                            Tự động sao lưu dữ liệu hàng ngày
                        </label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Giờ Sao Lưu</label>
                            <input type="time" class="form-control" name="backup_time" value="02:00">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="setting-item">
                            <label class="form-label">Số Bản Sao Lưu Giữ Lại</label>
                            <input type="number" class="form-control" name="backup_retention_days" value="30" min="1" max="365">
                        </div>
                    </div>
                </div>
                
                <div class="setting-item">
                    <label class="form-label">Sao Lưu Lên Cloud</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="cloud_backup" id="cloud_backup">
                        <label class="form-check-label" for="cloud_backup">
                            Tự động upload bản sao lưu lên cloud storage
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="settings-section">
                <h4 class="section-title">
                    <i class="fas fa-database me-2"></i>
                    Quản Lý Sao Lưu Dữ Liệu
                </h4>
                
                <!-- Backup Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $backupStats['total_backups'] ?? 0 }}</h3>
                                <p>Tổng Sao Lưu</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $backupStats['completed_backups'] ?? 0 }}</h3>
                                <p>Thành Công</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-hdd"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format(($backupStats['total_size'] ?? 0) / 1024 / 1024, 1) }} MB</h3>
                                <p>Tổng Dung Lượng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $backupStats['recent_backups'] ?? 0 }}</h3>
                                <p>7 Ngày Qua</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Backup Actions -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="createBackup()" id="createBackupBtn">
                            <i class="fas fa-plus me-2"></i>
                            Tạo Sao Lưu Ngay
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info w-100" onclick="showRestoreModal()">
                            <i class="fas fa-undo me-2"></i>
                            Khôi Phục Dữ Liệu
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success w-100" onclick="refreshBackupList()">
                            <i class="fas fa-sync me-2"></i>
                            Làm Mới Danh Sách
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-warning w-100" onclick="showBackupSettings()">
                            <i class="fas fa-cog me-2"></i>
                            Cài Đặt Sao Lưu
                        </button>
                    </div>
                </div>
                
                <!-- Backup List -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-list me-2"></i>Danh Sách Sao Lưu</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterBackups('all')">Tất Cả</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterBackups('manual')">Thủ Công</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="filterBackups('automatic')">Tự Động</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped" id="backupTable">
                            <thead>
                                <tr>
                                    <th>Tên File</th>
                                    <th>Loại</th>
                                    <th>Ngày Tạo</th>
                                    <th>Kích Thước</th>
                                    <th>Mô Tả</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody id="backupTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Đang tải...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Restore Backup Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">
                    <i class="fas fa-undo me-2"></i>Khôi Phục Dữ Liệu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cảnh báo:</strong> Thao tác này sẽ thay thế toàn bộ dữ liệu hiện tại bằng dữ liệu từ file sao lưu được chọn. Tất cả dữ liệu hiện tại sẽ bị mất!
                </div>
                <div class="mb-3">
                    <label for="restoreFileSelect" class="form-label">Chọn File Sao Lưu:</label>
                    <select class="form-select" id="restoreFileSelect">
                        <option value="">Đang tải danh sách...</option>
                    </select>
                </div>
                <div id="restoreFileInfo" class="card" style="display: none;">
                    <div class="card-body">
                        <h6>Thông Tin File Sao Lưu:</h6>
                        <p><strong>Tên File:</strong> <span id="restoreFileName"></span></p>
                        <p><strong>Kích Thước:</strong> <span id="restoreFileSize"></span></p>
                        <p><strong>Ngày Tạo:</strong> <span id="restoreFileDate"></span></p>
                        <p><strong>Loại:</strong> <span id="restoreFileType"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="confirmRestore()" id="confirmRestoreBtn" disabled>
                    <i class="fas fa-undo me-2"></i>Khôi Phục
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1" aria-labelledby="createBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBackupModalLabel">
                    <i class="fas fa-plus me-2"></i>Tạo Sao Lưu Mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="backupDescription" class="form-label">Mô Tả Sao Lưu:</label>
                    <textarea class="form-control" id="backupDescription" rows="3" placeholder="Nhập mô tả cho bản sao lưu này..."></textarea>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Quá trình tạo sao lưu có thể mất vài phút tùy thuộc vào kích thước cơ sở dữ liệu.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="confirmCreateBackup()">
                    <i class="fas fa-plus me-2"></i>Tạo Sao Lưu
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<style>
    .settings-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: white;
        font-size: 24px;
    }
    
    .stat-content h3 {
        margin: 0;
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }
    
    .stat-content p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    
    .backup-type-badge {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .backup-actions .btn {
        margin: 2px;
    }
    
    .settings-tabs {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-bottom: none;
        padding: 0 20px;
    }
    
    .settings-tabs .nav-link {
        color: rgba(255, 255, 255, 0.8);
        border: none;
        border-radius: 0;
        padding: 15px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .settings-tabs .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .settings-tabs .nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.2);
        border-bottom: 3px solid white;
    }
    
    .settings-content {
        padding: 30px;
    }
    
    .settings-section {
        margin-bottom: 40px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f8f9fa;
    }
    
    .settings-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .section-title {
        font-size: 18px;
        font-weight: bold;
        color: #343a40;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .setting-item {
        margin-bottom: 20px;
    }
    
    .setting-item .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    
    .setting-item .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }
    
    .setting-item .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .table {
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border: none;
        font-weight: 600;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
</style>

<script>
let allBackups = [];
let currentFilter = 'all';

// Load backup list on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBackupList();
});

// Load backup list
function loadBackupList() {
    console.log('Loading backup list...');
    fetch('{{ route("admin.settings.backup.test") }}')
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Backup data received:', data);
            allBackups = data;
            displayBackups(data);
        })
        .catch(error => {
            console.error('Error loading backups:', error);
            document.getElementById('backupTableBody').innerHTML = 
                '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải danh sách sao lưu: ' + error.message + '</td></tr>';
        });
}

// Display backups in table
function displayBackups(backups) {
    const tbody = document.getElementById('backupTableBody');
    
    if (backups.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Không có sao lưu nào</td></tr>';
        return;
    }
    
    tbody.innerHTML = backups.map(backup => `
        <tr>
            <td>
                <i class="fas fa-database me-2 text-primary"></i>
                ${backup.filename}
            </td>
            <td>
                <span class="badge backup-type-badge ${getTypeBadgeClass(backup.type)}">
                    ${getTypeLabel(backup.type)}
                </span>
            </td>
            <td>${backup.created_at}</td>
            <td>${backup.size}</td>
            <td>${backup.description || 'Không có mô tả'}</td>
            <td class="backup-actions">
                <button class="btn btn-sm btn-info" onclick="downloadBackup('${backup.filename}')" title="Tải về">
                    <i class="fas fa-download"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="restoreBackup('${backup.filename}')" title="Khôi phục">
                    <i class="fas fa-undo"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteBackup('${backup.filename}')" title="Xóa">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Get type badge class
function getTypeBadgeClass(type) {
    switch(type) {
        case 'manual': return 'bg-primary';
        case 'automatic': return 'bg-success';
        case 'scheduled': return 'bg-info';
        default: return 'bg-secondary';
    }
}

// Get type label
function getTypeLabel(type) {
    switch(type) {
        case 'manual': return 'Thủ Công';
        case 'automatic': return 'Tự Động';
        case 'scheduled': return 'Định Kỳ';
        default: return 'Khác';
    }
}

// Filter backups
function filterBackups(type) {
    currentFilter = type;
    
    // Update button states
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    let filteredBackups = allBackups;
    if (type !== 'all') {
        filteredBackups = allBackups.filter(backup => backup.type === type);
    }
    
    displayBackups(filteredBackups);
}

// Create backup
function createBackup() {
    const modal = new bootstrap.Modal(document.getElementById('createBackupModal'));
    modal.show();
}

// Confirm create backup
function confirmCreateBackup() {
    const description = document.getElementById('backupDescription').value;
    const btn = document.querySelector('#createBackupModal .btn-primary');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang tạo...';
    
    fetch('{{ route("admin.settings.backup.create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('createBackupModal')).hide();
            loadBackupList();
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Có lỗi xảy ra khi tạo sao lưu');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus me-2"></i>Tạo Sao Lưu';
    });
}

// Show restore modal
function showRestoreModal() {
    const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
    const select = document.getElementById('restoreFileSelect');
    
    // Load backup files
    select.innerHTML = '<option value="">Đang tải...</option>';
    
    fetch('{{ route("admin.settings.backup.list") }}')
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Chọn file sao lưu...</option>';
            data.forEach(backup => {
                select.innerHTML += `<option value="${backup.filename}" data-size="${backup.size}" data-date="${backup.created_at}" data-type="${backup.type}">${backup.filename}</option>`;
            });
        })
        .catch(error => {
            select.innerHTML = '<option value="">Lỗi khi tải danh sách</option>';
        });
    
    modal.show();
}

// Handle restore file selection
document.getElementById('restoreFileSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const infoDiv = document.getElementById('restoreFileInfo');
    const confirmBtn = document.getElementById('confirmRestoreBtn');
    
    if (this.value) {
        document.getElementById('restoreFileName').textContent = selectedOption.value;
        document.getElementById('restoreFileSize').textContent = selectedOption.dataset.size;
        document.getElementById('restoreFileDate').textContent = selectedOption.dataset.date;
        document.getElementById('restoreFileType').textContent = getTypeLabel(selectedOption.dataset.type);
        
        infoDiv.style.display = 'block';
        confirmBtn.disabled = false;
    } else {
        infoDiv.style.display = 'none';
        confirmBtn.disabled = true;
    }
});

// Confirm restore
function confirmRestore() {
    const filename = document.getElementById('restoreFileSelect').value;
    
    if (!filename) {
        showAlert('warning', 'Vui lòng chọn file sao lưu');
        return;
    }
    
    if (!confirm('Bạn có chắc chắn muốn khôi phục dữ liệu từ file này? Tất cả dữ liệu hiện tại sẽ bị mất!')) {
        return;
    }
    
    const btn = document.getElementById('confirmRestoreBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang khôi phục...';
    
    fetch('{{ route("admin.settings.backup.restore") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            backup_file: filename
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            bootstrap.Modal.getInstance(document.getElementById('restoreModal')).hide();
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Có lỗi xảy ra khi khôi phục dữ liệu');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-undo me-2"></i>Khôi Phục';
    });
}

// Download backup
function downloadBackup(filename) {
    window.open(`{{ route("admin.settings.backup.download", ":filename") }}`.replace(':filename', filename), '_blank');
}

// Delete backup
function deleteBackup(filename) {
    if (!confirm('Bạn có chắc chắn muốn xóa file sao lưu này?')) {
        return;
    }
    
    fetch(`{{ route("admin.settings.backup.delete", ":filename") }}`.replace(':filename', filename), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadBackupList();
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Có lỗi xảy ra khi xóa file sao lưu');
    });
}

// Refresh backup list
function refreshBackupList() {
    loadBackupList();
    showAlert('info', 'Đã làm mới danh sách sao lưu');
}

// Show backup settings
function showBackupSettings() {
    showAlert('info', 'Tính năng cài đặt sao lưu sẽ được phát triển trong phiên bản tiếp theo');
}

// Show alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.settings-container').insertBefore(alertDiv, document.querySelector('.settings-container').firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

<script>
    function saveAllSettings() {
        const formData = new FormData();
        
        // Collect all form data
        document.querySelectorAll('input, textarea, select').forEach(element => {
            if (element.type === 'checkbox') {
                formData.append(element.name, element.checked ? '1' : '0');
            } else if (element.type === 'radio') {
                if (element.checked) {
                    formData.append(element.name, element.value);
                }
            } else {
                formData.append(element.name, element.value);
            }
        });
        
        // Show loading state
        const saveBtn = event.target;
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang lưu...';
        saveBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
            
            // Show success message
            showAlert('success', 'Cài đặt đã được lưu thành công!');
        }, 2000);
    }
    
    function createBackup() {
        if (confirm('Bạn có chắc chắn muốn tạo bản sao lưu ngay bây giờ?')) {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang tạo...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                showAlert('success', 'Bản sao lưu đã được tạo thành công!');
            }, 3000);
        }
    }
    
    function restoreBackup() {
        if (confirm('Bạn có chắc chắn muốn khôi phục từ bản sao lưu? Thao tác này sẽ ghi đè dữ liệu hiện tại.')) {
            showAlert('warning', 'Chức năng khôi phục đang được phát triển');
        }
    }
    
    function downloadBackup() {
        showAlert('info', 'Chức năng tải sao lưu đang được phát triển');
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.settings-content').insertBefore(alertDiv, document.querySelector('.settings-content').firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Auto-save functionality
    let autoSaveTimeout;
    document.querySelectorAll('input, textarea, select').forEach(element => {
        element.addEventListener('change', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Auto-save individual setting
                console.log('Auto-saving setting:', this.name, this.value);
            }, 2000);
        });
    });
    
    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                showAlert('danger', 'Vui lòng điền đầy đủ thông tin bắt buộc');
                return;
            }
            
            // Process form submission
            saveAllSettings();
        });
    });
</script>
@endsection
