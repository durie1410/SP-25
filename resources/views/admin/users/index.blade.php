@extends('layouts.admin')

@section('title', 'Người Dùng - Admin')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-users" style="color: #22c55e;"></i>
            Quản lý và theo dõi người dùng
        </h1>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="background: #22c55e; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-user-plus"></i>
            Thêm Người Dùng
        </a>
      
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h6 style="font-size: 13px; font-weight: 700; text-transform: uppercase; margin: 0 0 16px 0; letter-spacing: 0.5px; line-height: 1.4; opacity: 0.9;">TỔNG NGƯỜI DÙNG</h6>
        <h3 style="font-size: 42px; font-weight: 700; margin: 0 0 8px 0; line-height: 1.2;">{{ $totalUsers }}</h3>
        <div style="margin-top: 12px; font-size: 14px; opacity: 0.9;">
            <i class="fas fa-user-plus"></i> {{ $newUsersThisMonth }} mới trong tháng
        </div>
        <div style="position: absolute; bottom: 24px; right: 24px; opacity: 0.3;">
            <i class="fas fa-users" style="font-size: 64px;"></i>
        </div>
    </div>

    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0 0 16px 0; letter-spacing: 0.5px; line-height: 1.4;">PHÂN LOẠI NGƯỜI DÙNG</h6>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #6b7280; font-size: 14px;"><i class="fas fa-crown" style="color: #dc3545;"></i> Quản trị viên</span>
                <strong style="color: #1f2937; font-size: 18px;">{{ $adminUsers }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #6b7280; font-size: 14px;"><i class="fas fa-user-tie" style="color: #f59e0b;"></i> Nhân viên</span>
                <strong style="color: #1f2937; font-size: 18px;">{{ $staffUsers }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #6b7280; font-size: 14px;"><i class="fas fa-user" style="color: #17a2b8;"></i> Người dùng</span>
                <strong style="color: #1f2937; font-size: 18px;">{{ $regularUsers }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 25px; background: white; border: 1px solid #e5e7eb; border-radius: 12px;">
    <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px;">
        <h3 class="card-title" style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
            <i class="fas fa-search" style="color: #22c55e; margin-right: 8px;"></i>
            Tìm kiếm & Lọc
        </h3>
    </div>
    <form method="GET" action="{{ route('admin.users.index') }}" style="padding: 0 25px 25px 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Tìm kiếm người dùng</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tên, email..." style="width: 100%;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; color: #374151;">Vai trò</label>
                <select name="role" class="form-control" style="width: 100%;">
                    <option value="">-- Tất cả vai trò --</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Người dùng</option>
                </select>
            </div>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-start;">
            <button type="submit" class="btn btn-primary" style="background: #22c55e; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-filter"></i>
                Lọc
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="background: white; color: #1f2937; border: 1px solid #e5e7eb; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-redo"></i>
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="admin-table">
    
    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Thông tin</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hoạt động cuối</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge role-{{ $user->role }}">
                            @switch($user->role)
                                @case('admin')
                                    <i class="fas fa-crown me-1"></i>Quản trị viên
                                    @break
                                @case('staff')
                                    <i class="fas fa-user-tie me-1"></i>Nhân viên
                                    @break
                                @default
                                    <i class="fas fa-user me-1"></i>Người dùng
                            @endswitch
                        </span>
                    </td>
                    <td>
                        @if($user->isLocked())
                            <span class="status-badge" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
                                <i class="fas fa-lock me-1"></i>Đã khóa
                            </span>
                            <div style="margin-top: 6px; font-size: 12px; color: #b91c1c; max-width: 280px; line-height: 1.4;">
                                {{ $user->locked_reason ?: 'Không có lý do khóa' }}
                            </div>
                            @if($user->locked_at)
                                <div style="font-size: 11px; color: #6b7280; margin-top: 3px;">
                                    Khóa lúc: {{ $user->locked_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        @else
                            <span class="status-badge status-active">
                                <i class="fas fa-check-circle me-1"></i>Hoạt động
                            </span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>{{ $user->updated_at->diffForHumans() }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-info btn-sm" onclick="viewUser({{ $user->id }})" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($user->id !== auth()->id())
                            <button class="btn btn-sm {{ $user->isLocked() ? 'btn-success' : 'btn-danger' }} btn-sm"
                                    onclick="toggleLock({{ $user->id }}, {{ $user->isLocked() ? 'true' : 'false' }})"
                                    title="{{ $user->isLocked() ? 'Mở khóa' : 'Khóa' }}">
                                @if($user->isLocked())
                                    <i class="fas fa-unlock"></i>
                                @else
                                    <i class="fas fa-lock"></i>
                                @endif
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>Chưa có người dùng nào</h5>
                            <p class="text-muted">Bắt đầu bằng cách thêm người dùng đầu tiên</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if(isset($users) && $users->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- Modal Chi tiết người dùng -->
<div class="modal-overlay" id="userDetailModal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-user"></i> Chi tiết người dùng
            </h5>
            <button type="button" class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <div class="modal-body" id="userDetailContent">
            <div class="text-center">
                <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div>
                <p>Đang tải...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Đóng</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Định nghĩa route URLs - sử dụng base URL từ window.location
    (function() {
        const origin = window.location.origin;
        const currentPath = window.location.pathname;
        const basePath = currentPath.substring(0, currentPath.lastIndexOf('/admin/users'));
        
        window.adminUsersRoutes = {
            show: (id) => `${origin}${basePath}/admin/users/${id}`,
            lock: (id) => `${origin}${basePath}/admin/users/lock/${id}`,
            unlock: (id) => `${origin}${basePath}/admin/users/unlock/${id}`
        };
        
        console.log('Origin:', origin);
        console.log('Base path:', basePath);
        console.log('Sample show route:', window.adminUsersRoutes.show(1));
    })();
</script>
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .page-subtitle {
        font-size: 1rem;
        margin: 10px 0 0 0;
        opacity: 0.9;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    
    .user-details {
        flex: 1;
    }
    
    .user-name {
        font-weight: 600;
        color: #343a40;
        margin-bottom: 2px;
    }
    
    .user-email {
        font-size: 12px;
        color: #6c757d;
    }
    
    .role-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .role-admin {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .role-staff {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .role-user {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-active {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .status-inactive {
        background: linear-gradient(135deg, #6c757d, #5a6268);
        color: white;
    }
    
    .empty-state {
        padding: 40px;
    }
    
    .empty-state i {
        opacity: 0.5;
    }
    
    .btn-group .btn {
        margin: 0 1px;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }
    
    .modal-header .btn-close {
        filter: invert(1);
    }
    
    .user-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin: 0 auto;
    }
    
    .user-detail strong {
        color: #495057;
        display: block;
        margin-bottom: 5px;
    }
    
    .user-detail p {
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }
    
    .modal-container {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.3s ease;
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border-radius: 12px 12px 0 0;
    }
    
    .modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    
    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    
    .modal-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .modal-body {
        padding: 30px;
    }
    
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    // Đảm bảo các hàm có thể gọi từ onclick
    window.viewUser = function(userId) {
        console.log('Viewing user:', userId); // Debug
        
        // Hiển thị modal và loading
        const modal = document.getElementById('userDetailModal');
        const content = document.getElementById('userDetailContent');
        
        if (!modal || !content) {
            alert('Không thể tìm thấy modal. Vui lòng tải lại trang.');
            console.error('Modal elements not found');
            return;
        }
        
        content.innerHTML = '<div class="text-center"><div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto;"></div><p>Đang tải...</p></div>';
        modal.style.display = 'flex';
        
        // Gọi API để lấy thông tin user
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('Không tìm thấy CSRF token. Vui lòng tải lại trang.');
            return;
        }
        
        fetch(window.adminUsersRoutes.show(userId), {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`Lỗi ${response.status}: Không thể tải thông tin người dùng`);
                });
            }
            return response.json();
        })
        .then(data => {
            // Hiển thị thông tin user
            const roleText = data.role === 'admin' ? 'Quản trị viên' : (data.role === 'staff' ? 'Nhân viên' : 'Người dùng');
            const roleBadge = data.role === 'admin'
                ? '<span class="badge bg-danger">Quản trị viên</span>'
                : (data.role === 'staff'
                    ? '<span class="badge" style="background:#f59e0b; color:white;">Nhân viên</span>'
                    : '<span class="badge bg-secondary">Người dùng</span>');
            
            const lockedBadge = data.is_locked
                ? '<span class="badge" style="background:#ef4444; color:white;"><i class="fas fa-lock me-1"></i>Đã khóa</span>'
                : '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Hoạt động</span>';

            const lockReasonHtml = data.is_locked
                ? `<p class="mb-0" style="color:#b91c1c;">${data.locked_reason || 'Không có lý do khóa'}</p>`
                : '<p class="mb-0"><span style="color:#9ca3af;">Không áp dụng</span></p>';

            const lockedAtHtml = data.locked_at
                ? new Date(data.locked_at).toLocaleString('vi-VN')
                : '<span style="color:#9ca3af;">Không áp dụng</span>';

            content.innerHTML = `
                <div class="user-detail">
                    <div class="text-center mb-4">
                        <div class="user-avatar-large mx-auto mb-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>${data.name}</h4>
                        <p class="text-muted">${data.email}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-id-card me-2"></i>ID:</strong>
                            <p class="mb-0">${data.id}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-user-tag me-2"></i>Vai trò:</strong>
                            <p class="mb-0">${roleBadge}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-shield-alt me-2"></i>Trạng thái:</strong>
                            <p class="mb-0">${lockedBadge}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-clock me-2"></i>Thời điểm khóa:</strong>
                            <p class="mb-0">${lockedAtHtml}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <strong><i class="fas fa-comment-dots me-2"></i>Lý do khóa:</strong>
                            ${lockReasonHtml}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-calendar-plus me-2"></i>Ngày tạo:</strong>
                            <p class="mb-0">${new Date(data.created_at).toLocaleDateString('vi-VN')}</p>
                        </div>
                    </div>

                    <hr style="margin: 20px 0; border-top: 2px solid #e5e7eb;">

                    <h6 style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 15px;">
                        <i class="fas fa-address-card me-2"></i>Thông tin đăng ký độc giả
                    </h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-phone me-2"></i>Số điện thoại:</strong>
                            <p class="mb-0">${data.phone || '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-calendar-alt me-2"></i>Ngày sinh:</strong>
                            <p class="mb-0">${data.ngay_sinh ? new Date(data.ngay_sinh).toLocaleDateString('vi-VN') : '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-venus-mars me-2"></i>Giới tính:</strong>
                            <p class="mb-0">${data.gioi_tinh ? (data.gioi_tinh === 'male' ? 'Nam' : (data.gioi_tinh === 'female' ? 'Nữ' : data.gioi_tinh)) : '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="fas fa-id-card me-2"></i>Số CCCD:</strong>
                            <p class="mb-0">${data.so_cccd || '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <strong><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:</strong>
                            <p class="mb-0">${data.address || '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong><i class="fas fa-map me-2"></i>Tỉnh/Thành:</strong>
                            <p class="mb-0">${data.province || '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong><i class="fas fa-map-pin me-2"></i>Quận/Huyện:</strong>
                            <p class="mb-0">${data.district || '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong><i class="fas fa-building me-2"></i>Phường/Xã:</strong>
                            <p class="mb-0">${data.xa || '<span style="color:#9ca3af;">Chưa cập nhật</span>'}</p>
                        </div>
                    </div>

                    ${data.cccd_image ? `
                    <div class="mt-3">
                        <strong><i class="fas fa-image me-2"></i>Ảnh CCCD:</strong>
                        <div class="mt-2">
                            <img src="${data.cccd_image.startsWith('http') ? data.cccd_image : '/storage/' + data.cccd_image}" alt="Ảnh CCCD" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid #e5e7eb;" onerror="this.style.display='none'">
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="alert alert-danger" style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Lỗi:</strong> ${error.message || 'Không thể tải thông tin người dùng. Vui lòng thử lại sau.'}
                    <br><small>Vui lòng mở Console (F12) để xem chi tiết lỗi.</small>
                </div>
            `;
        });
    }
    
    window.closeUserModal = function() {
        const modal = document.getElementById('userDetailModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Đợi DOM load xong
    document.addEventListener('DOMContentLoaded', function() {
        // Đóng modal khi click bên ngoài
        const modal = document.getElementById('userDetailModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeUserModal();
                }
            });
        }
        
        // Đóng modal khi nhấn ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUserModal();
            }
        });
    });
    
    window.toggleLock = function(userId, isLocked) {
        const actionText = isLocked ? 'mở khóa' : 'khóa';
        const actionRoute = isLocked ? window.adminUsersRoutes.unlock : window.adminUsersRoutes.lock;
        const method = 'GET';

        if (!confirm(`Bạn có chắc chắn muốn ${actionText} tài khoản này?`)) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('Không tìm thấy CSRF token. Vui lòng tải lại trang.');
            return;
        }

        fetch(actionRoute(userId), {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            }
            if (response.ok) {
                return { success: true, message: `Đã ${actionText} tài khoản!` };
            }
            return response.text().then(text => {
                console.error('Error:', text);
                throw new Error(`Lỗi ${response.status}`);
            });
        })
        .then(data => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra: ' + (error.message || 'Lỗi không xác định'));
        });
    }
    
    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
</script>
@endpush
