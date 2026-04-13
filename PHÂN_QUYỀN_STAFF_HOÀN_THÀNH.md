# 📊 PHÂN QUYỀN STAFF - HOÀN THÀNH ✅

## ✨ CÁC THAY ĐỔI ĐÃ THỰC HIỆN

### 1. ✅ Cập nhật RolePermissionSeeder.php
- **File**: `database/seeders/RolePermissionSeeder.php`
- **Thay đổi**: Thêm STAFF role với 17 quyền
- **Lệnh chạy**: `php artisan db:seed --class=RolePermissionSeeder`

### 2. ✅ Tạo Staff User
- **Email**: staff@library.com
- **Password**: 123456
- **Role**: staff

### 3. ✅ Gán Permissions cho STAFF

STAFF có thể:
- ✓ Xem & quản lý **đơn hàng** (mượn/trả sách)
- ✓ Xem & quản lý **sách** (thêm mới, sửa, xem)
- ✓ Xem & xuất **báo cáo**
- ✓ Xác nhận **đặt chỗ**
- ✓ Phê duyệt **đánh giá**

STAFF KHÔNG thể:
- ✗ Xóa sách
- ✗ Xóa đơn hàng
- ✗ Quản lý người dùng
- ✗ Truy cập cài đặt hệ thống

---

## 📋 DANH SÁCH PERMISSIONS CỦA STAFF (17)

```
Dashboard
└─ view-dashboard ........................... Xem dashboard

Books (Sách)
├─ view-books .............................. Xem sách
├─ create-books ............................ Thêm sách mới
└─ edit-books .............................. Sửa thông tin sách

Categories (Danh mục)
└─ view-categories ......................... Xem danh mục

Orders/Borrows (Đơn hàng/Mượn)
├─ view-borrows ............................ Xem đơn hàng
├─ create-borrows .......................... Tạo đơn hàng mới
├─ edit-borrows ............................ Chỉnh sửa đơn hàng
└─ return-books ............................ Xử lý trả sách

Reservations (Đặt chỗ)
├─ view-reservations ....................... Xem đặt chỗ
└─ confirm-reservations .................... Xác nhận đặt chỗ

Readers (Độc giả)
└─ view-readers ............................ Xem độc giả

Reports (Báo cáo)
├─ view-reports ............................ Xem báo cáo
└─ export-reports .......................... Xuất báo cáo

Notifications (Thông báo)
└─ view-notifications ....................... Xem thông báo

Reviews (Đánh giá)
├─ view-reviews ............................ Xem đánh giá
└─ approve-reviews ......................... Phê duyệt đánh giá
```

---

## 🔐 CÁCH BẢO VỆ ROUTES

### Trong `routes/web.php`:

```php
// Chỉ cho phép staff xem đơn hàng
Route::get('/admin/orders', [OrderController::class, 'index'])
    ->middleware('permission:view-borrows');

// Chỉ cho phép staff sửa đơn hàng
Route::post('/admin/orders/{id}', [OrderController::class, 'update'])
    ->middleware('permission:edit-borrows');

// Hoặc dùng middleware staff đã có sẵn
Route::middleware(['auth', 'staff'])->group(function () {
    Route::resource('orders', OrderController::class);
    Route::resource('books', BookController::class);
    Route::get('reports', [ReportController::class, 'index']);
});
```

---

## 📝 CÁCH KIỂM SOÁT VIEW TRONG BLADE

```blade
<!-- Chỉ hiện cho staff -->
@can('view-borrows')
    <a href="{{ route('orders.index') }}">
        <i class="icon-shopping-cart"></i> Quản lý đơn hàng
    </a>
@endcan

<!-- Chỉ hiện nút Edit cho staff -->
@can('edit-books')
    <button onclick="editBook()">Sửa</button>
@endcan

<!-- Không hiện cho staff -->
@cannot('delete-books')
    <!-- Nút xóa không hiện cho staff -->
@endcannot
```

---

## 🎯 CÁC FILE ĐÃ TẠO/THAY ĐỔI

| File | Mục đích |
|------|----------|
| `database/seeders/RolePermissionSeeder.php` | ✏️ Cập nhật seeder thêm staff role |
| `PHÂN_QUYỀN_STAFF.md` | 📖 Hướng dẫn chi tiết (17KB) |
| `PHÂN_QUYỀN_STAFF.sql` | 🗄️ Script SQL gán quyền |
| `assign_staff_role.php` | 🔧 Script gán role cho user |
| `check_staff_permissions.php` | ✓ Script kiểm tra quyền |
| `PHÂN_QUYỀN_STAFF.bat` | ⚡ Batch file chạy nhanh |
| `HƯỚNG_DẪN_PHÂN_QUYỀN_STAFF.txt` | 📋 Hướng dẫn quick start |

---

## 🚀 BƯỚC TIẾP THEO

### 1. Cập nhật Routes (nếu chưa có)

Thêm middleware `permission:` vào routes cần bảo vệ:

```php
Route::middleware(['permission:view-borrows'])->get('/orders', ...);
Route::middleware(['permission:view-books'])->get('/books', ...);
Route::middleware(['permission:view-reports'])->get('/reports', ...);
```

### 2. Cập nhật Blade Templates

Sử dụng `@can` directive để kiểm soát hiển thị:

```blade
@can('view-borrows')
    <!-- Hiện menu đơn hàng cho staff -->
@endcan
```

### 3. Kiểm Thử

Đăng nhập với tài khoản staff:
- **Email**: staff@library.com
- **Password**: 123456

Xác nhận rằng staff có thể:
- ✅ Xem đơn hàng
- ✅ Thêm sách mới
- ✅ Xem báo cáo
- ❌ Xóa người dùng (không có quyền)

---

## 📞 HƯỚNG DẪN NÂNG CAO

### Thêm quyền mới cho staff:

```bash
php artisan tinker
```

```php
$staffRole = Spatie\Permission\Models\Role::findByName('staff');
$staffRole->givePermissionTo('delete-books');
```

### Loại bỏ quyền từ staff:

```php
$staffRole = Spatie\Permission\Models\Role::findByName('staff');
$staffRole->revokePermissionTo('delete-books');
```

### Gán staff role cho user hiện tại:

```php
$user = App\Models\User::find(2);  // Thay 2 bằng ID
$user->assignRole('staff');
$user->update(['role' => 'staff']);
```

---

## ✅ KẾT QUẢ CUỐI CÙNG

```
╔════════════════════════════════════════════╗
║  PHÂN QUYỀN STAFF HOÀN THÀNH               ║
╠════════════════════════════════════════════╣
║  ✅ Role Staff: 17 quyền                   ║
║  ✅ User Staff: staff@library.com          ║
║  ✅ Seeders: Cập nhật                      ║
║  ✅ Migrations: Sẵn sàng                   ║
║  ✅ Tài liệu: Đầy đủ                      ║
╚════════════════════════════════════════════╝
```

---

**Tạo lúc**: 26/01/2026  
**Version**: 1.0  
**Status**: ✅ Hoàn thành
