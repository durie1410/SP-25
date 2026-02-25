## 📋 Hướng dẫn phân quyền cho STAFF

Dưới đây là hướng dẫn chi tiết để phân quyền cho nhân viên (staff) quản lý đơn hàng, sách và xem báo cáo.

---

## 1️⃣ Hiểu cấu trúc phân quyền hiện tại

Hệ thống sử dụng **Spatie Permission** với cấu trúc:
- **Users** → **Roles** → **Permissions**
- File cấu hình: `database/seeders/RolePermissionSeeder.php`
- Middleware kiểm tra: `app/Http/Middleware/PermissionMiddleware.php`

### Các role hiện tại:
1. **admin** - Toàn quyền
2. **user** - Quyền chỉ đọc (xem sách, tạo đánh giá, đặt chỗ)

---

## 2️⃣ Thêm Role STAFF với Quyền Phù Hợp

### Bước 1: Chỉnh sửa `RolePermissionSeeder.php`

Mở file: `database/seeders/RolePermissionSeeder.php`

**Tìm section `$roles`** (khoảng dòng 150-190) và **thêm staff role** vào danh sách roles:

```php
// ========== STAFF (Nhân viên) - Quyền trung bình ==========
'staff' => [
    // Dashboard
    'view-dashboard',
    
    // Books - Có thể xem, tạo và sửa
    'view-books', 'create-books', 'edit-books',
    
    // Categories - Chỉ xem
    'view-categories',
    
    // Orders/Borrows - Quản lý đơn hàng
    'view-borrows', 'create-borrows', 'edit-borrows', 'return-books',
    
    // Reservations - Quản lý đặt chỗ
    'view-reservations', 'confirm-reservations',
    
    // Readers - Có thể xem
    'view-readers',
    
    // Reports - Xem báo cáo
    'view-reports', 'export-reports',
    
    // Notifications - Xem thông báo
    'view-notifications',
    
    // Reviews - Có thể xem và phê duyệt
    'view-reviews', 'approve-reviews',
],
```

### Bước 2: Cập nhật lại file seeder bằng lệnh:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## 3️⃣ Gán Role STAFF cho User

### Cách 1: Dùng Tinker (nhanh)
```bash
php artisan tinker
```

Sau đó chạy:
```php
$user = App\Models\User::find(ID_CUA_STAFF);  // Thay ID_CUA_STAFF bằng ID thực
$user->assignRole('staff');
$user->update(['role' => 'staff']);
exit
```

### Cách 2: Dùng SQL trực tiếp
```sql
-- Gán role staff cho user có ID = 2
INSERT INTO model_has_roles (role_id, model_type, model_id) 
VALUES (3, 'App\\Models\\User', 2);

-- Cập nhật cột role trong users
UPDATE users SET role = 'staff' WHERE id = 2;
```

### Cách 3: Dùng Migration (cách chuyên nghiệp)

Tạo file migration mới:
```bash
php artisan make:migration assign_staff_role_to_user
```

Nội dung file:
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration {
    public function up(): void
    {
        // Cập nhật user có email = staff@library.com thành staff
        $user = User::where('email', 'staff@library.com')->first();
        if ($user) {
            $user->assignRole('staff');
            $user->update(['role' => 'staff']);
        }
    }

    public function down(): void
    {
        $user = User::where('email', 'staff@library.com')->first();
        if ($user) {
            $user->removeRole('staff');
            $user->update(['role' => 'user']);
        }
    }
};
```

Chạy migration:
```bash
php artisan migrate
```

---

## 4️⃣ Bảo vệ Routes cho STAFF

### Cách sử dụng middleware trong routes

Mở `routes/web.php` và áp dụng middleware permission:

```php
Route::middleware(['auth', 'permission:view-borrows'])->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/admin/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'permission:edit-borrows'])->group(function () {
    Route::post('/admin/orders/{id}/update', [OrderController::class, 'update'])->name('orders.update');
});
```

### Hoặc dùng StaffMiddleware đã có sẵn

```php
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/staff/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');
    Route::resource('staff/orders', OrderController::class);
});
```

---

## 5️⃣ Kiểm tra quyền trong Blade Template

```blade
@can('view-borrows')
    <a href="{{ route('orders.index') }}">Quản lý đơn hàng</a>
@endcan

@can('edit-books')
    <button onclick="editBook()">Sửa sách</button>
@endcan

@can('view-reports')
    <a href="{{ route('reports.index') }}">Xem báo cáo</a>
@endcan
```

### Kiểm tra một trong nhiều quyền:
```blade
@canany(['edit-books', 'edit-borrows'])
    <div>Có quyền sửa sách hoặc đơn hàng</div>
@endcanany
```

---

## 6️⃣ Kiểm tra quyền trong Controller

```php
<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // Kiểm tra quyền
        if (!auth()->user()->can('view-borrows')) {
            abort(403, 'Không có quyền xem đơn hàng');
        }
        
        // Hoặc dùng cách này (sạch hơn)
        $this->authorize('view-borrows');
        
        // Logic xem danh sách đơn hàng
        return view('orders.index');
    }
    
    public function update(Request $request, $id)
    {
        $this->authorize('edit-borrows');
        
        // Logic cập nhật đơn hàng
    }
}
```

---

## 7️⃣ Danh sách Permissions cho STAFF

### Quản lý đơn hàng (Orders/Borrows):
- ✅ `view-borrows` - Xem danh sách đơn hàng
- ✅ `create-borrows` - Tạo đơn hàng
- ✅ `edit-borrows` - Chỉnh sửa đơn hàng
- ❌ `delete-borrows` - KHÔNG xóa (dành cho admin)
- ✅ `return-books` - Xử lý trả sách

### Quản lý sách:
- ✅ `view-books` - Xem danh sách sách
- ✅ `create-books` - Thêm sách mới
- ✅ `edit-books` - Chỉnh sửa thông tin sách
- ❌ `delete-books` - KHÔNG xóa (dành cho admin)

### Xem báo cáo:
- ✅ `view-reports` - Xem báo cáo
- ✅ `export-reports` - Xuất báo cáo

### Quản lý đặt chỗ:
- ✅ `view-reservations` - Xem đặt chỗ
- ✅ `confirm-reservations` - Xác nhận đặt chỗ

---

## 8️⃣ Thêm STAFF vào Menu Admin (nếu có)

Nếu có menu admin, cập nhật file view menu:

```blade
<!-- resources/views/admin/partials/sidebar.blade.php -->

@can('view-borrows')
    <li>
        <a href="{{ route('orders.index') }}">
            <i class="icon-shopping-cart"></i> Quản lý đơn hàng
        </a>
    </li>
@endcan

@can('view-books')
    <li>
        <a href="{{ route('books.index') }}">
            <i class="icon-book"></i> Quản lý sách
        </a>
    </li>
@endcan

@can('view-reports')
    <li>
        <a href="{{ route('reports.index') }}">
            <i class="icon-chart"></i> Báo cáo
        </a>
    </li>
@endcan
```

---

## 9️⃣ Kiểm tra quyền của user (Debugging)

Chạy lệnh sau để xem quyền của một user:

```bash
php artisan tinker
```

```php
$user = App\Models\User::find(2); // Thay 2 bằng ID staff
$user->role;        // Xem role
$user->getRoleNames();  // Xem tất cả role
$user->getPermissionNames(); // Xem tất cả quyền
$user->can('view-borrows'); // Kiểm tra 1 quyền cụ thể
exit
```

---

## 🔟 Tạo Staff User mới (nếu cần)

```bash
php artisan tinker
```

```php
$staff = App\Models\User::create([
    'name' => 'Nguyễn Văn A',
    'email' => 'staff1@library.com',
    'password' => bcrypt('password123'),
    'role' => 'staff'
]);

$staff->assignRole('staff');
dd('Staff user created successfully');
exit
```

---

## 📊 Tóm tắt Quyền STAFF

| Chức năng | Permission | Quyền | Ghi chú |
|-----------|-----------|-------|---------|
| Xem đơn hàng | view-borrows | ✅ | Bắt buộc |
| Tạo đơn hàng | create-borrows | ✅ | Có thể tắt nếu cần |
| Sửa đơn hàng | edit-borrows | ✅ | Có thể tắt nếu cần |
| Xóa đơn hàng | delete-borrows | ❌ | Chỉ admin |
| Trả sách | return-books | ✅ | Bắt buộc |
| Xem sách | view-books | ✅ | Bắt buộc |
| Thêm sách | create-books | ✅ | Tùy chọn |
| Sửa sách | edit-books | ✅ | Tùy chọn |
| Xóa sách | delete-books | ❌ | Chỉ admin |
| Xem báo cáo | view-reports | ✅ | Bắt buộc |
| Xuất báo cáo | export-reports | ✅ | Bắt buộc |

---

## 🎯 Bước Tiếp Theo

1. ✅ Chỉnh sửa `RolePermissionSeeder.php` thêm staff role
2. ✅ Chạy `php artisan db:seed --class=RolePermissionSeeder`
3. ✅ Gán role staff cho user (Tinker hoặc SQL)
4. ✅ Cập nhật routes với middleware `permission:view-borrows` v.v
5. ✅ Cập nhật Blade template với `@can` directives
6. ✅ Test quyền hạn với các tài khoản khác nhau

---

## ❓ Câu hỏi thường gặp

**Q: Làm sao để staff KHÔNG xóa được đơn hàng?**
A: Không gán permission `delete-borrows` cho role staff

**Q: Làm sao để tạm dừng quyền của staff?**
A: Dùng tinker: `$user->removeRole('staff')` rồi gán lại `user` role

**Q: Làm sao cấp thêm quyền cho staff mà không tạo migration?**
A: Dùng tinker:
```php
$role = Spatie\Permission\Models\Role::findByName('staff');
$role->givePermissionTo('delete-books');
```

**Q: Quyền có được cache không?**
A: Có! Nếu thay đổi quyền mà không thấy hiệu quả, chạy:
```bash
php artisan cache:clear
```
