<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permissions - Phân loại rõ ràng theo module
        $permissions = [
            // ========== DASHBOARD ==========
            'view-dashboard',
            
            // ========== BOOKS (Sách) ==========
            'view-books',
            'create-books',
            'edit-books',
            'delete-books',
            
            // ========== CATEGORIES (Danh mục) ==========
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            
            // ========== READERS (Độc giả) ==========
            'view-readers',
            'create-readers',
            'edit-readers',
            'delete-readers',
            
            // ========== BORROWS (Mượn trả) ==========
            'view-borrows',
            'create-borrows',
            'edit-borrows',
            'delete-borrows',
            'return-books',
            
            // ========== RESERVATIONS (Đặt chỗ) ==========
            'view-reservations',
            'create-reservations',
            'edit-reservations',
            'delete-reservations',
            'confirm-reservations',
            
            // ========== REVIEWS (Đánh giá) ==========
            'view-reviews',
            'create-reviews',
            'edit-reviews',
            'delete-reviews',
            'approve-reviews',
            
            // ========== FINES (Phạt) ==========
            'view-fines',
            'create-fines',
            'edit-fines',
            'delete-fines',
            'waive-fines',
            
            // ========== REPORTS (Báo cáo) ==========
            'view-reports',
            'export-reports',
            
            // ========== NOTIFICATIONS (Thông báo) ==========
            'view-notifications',
            'send-notifications',
            'manage-templates',
            
            // ========== USERS & ROLES (Người dùng & Vai trò) ==========
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-roles',
            'manage-permissions',
            
            // ========== INVENTORY (Kho sách) ==========
            'view-inventory',
            'create-inventory',
            'edit-inventory',
            'delete-inventory',
            'import-inventory',
            'export-inventory',
            
            // ========== SETTINGS (Cài đặt) ==========
            'view-settings',
            'edit-settings',
            'manage-settings',
            
            // ========== BACKUP (Sao lưu) ==========
            'view-backup',
            'create-backup',
            'restore-backup',
            'delete-backup',
            'manage-backup',
            
            // ========== LOGS (Nhật ký) ==========
            'view-logs',
            'manage-logs',
            
            // ========== BULK OPERATIONS (Thao tác hàng loạt) ==========
            'manage-bulk-operations',
            
            // ========== EMAIL MARKETING (Email marketing) ==========
            'view-email-marketing',
            'create-email-marketing',
            'edit-email-marketing',
            'send-email-marketing',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Tạo roles với 2 vai trò chính: admin, user
        // ========== ADMIN (Quản trị viên) - Toàn quyền ==========
        $roles = [
            'admin' => [
                // Dashboard
                'view-dashboard',
                
                // Books - Toàn quyền
                'view-books', 'create-books', 'edit-books', 'delete-books',
                
                // Categories - Toàn quyền
                'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
                
                // Readers - Toàn quyền
                'view-readers', 'create-readers', 'edit-readers', 'delete-readers',
                
                // Borrows - Toàn quyền
                'view-borrows', 'create-borrows', 'edit-borrows', 'delete-borrows', 'return-books',
                
                // Reservations - Toàn quyền
                'view-reservations', 'create-reservations', 'edit-reservations', 'delete-reservations', 'confirm-reservations',
                
                // Reviews - Toàn quyền
                'view-reviews', 'create-reviews', 'edit-reviews', 'delete-reviews', 'approve-reviews',
                
                // Fines - Toàn quyền
                'view-fines', 'create-fines', 'edit-fines', 'delete-fines', 'waive-fines',
                
                // Reports - Toàn quyền
                'view-reports', 'export-reports',
                
                // Notifications - Toàn quyền
                'view-notifications', 'send-notifications', 'manage-templates',
                
                // Users & Roles - Toàn quyền
                'view-users', 'create-users', 'edit-users', 'delete-users', 'manage-roles', 'manage-permissions',
                
                // Inventory - Toàn quyền
                'view-inventory', 'create-inventory', 'edit-inventory', 'delete-inventory', 'import-inventory', 'export-inventory',
                
                // Settings - Toàn quyền
                'view-settings', 'edit-settings', 'manage-settings',
                
                // Backup - Toàn quyền
                'view-backup', 'create-backup', 'restore-backup', 'delete-backup', 'manage-backup',
                
                // Logs - Toàn quyền
                'view-logs', 'manage-logs',
                
                // Bulk Operations - Toàn quyền
                'manage-bulk-operations',
                
                // Email Marketing - Toàn quyền
                'view-email-marketing', 'create-email-marketing', 'edit-email-marketing', 'send-email-marketing',
            ],
            
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
            
            // ========== USER (Người dùng) - Quyền hạn chế nhất ==========
            'user' => [
                // Chỉ xem sách và danh mục
                'view-books',
                'view-categories',
                
                // Có thể tạo đánh giá
                'create-reviews',
                
                // Có thể xem và tạo đặt chỗ
                'view-reservations', 'create-reservations',
                
                // Chỉ xem thông báo
                'view-notifications',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
            $this->command->info("Role '{$roleName}' created/updated with " . count($rolePermissions) . " permissions.");
        }

        // Gán role cho admin user
        $adminUser = User::where('email', 'admin@library.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
            $adminUser->update(['role' => 'admin']);
            $this->command->info('Admin user assigned admin role.');
        }

        // Tạo thêm user mẫu với role user
        $user = User::firstOrCreate([
            'email' => 'user@library.com'
        ], [
            'name' => 'Người dùng',
            'password' => bcrypt('123456'),
            'role' => 'user',
        ]);
        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }
        $user->update(['role' => 'user']);
        $this->command->info('User created/updated.');

        // Tạo thêm user mẫu với role staff
        $staffUser = User::firstOrCreate([
            'email' => 'staff@library.com'
        ], [
            'name' => 'Nhân viên',
            'password' => bcrypt('123456'),
            'role' => 'staff',
        ]);
        if (!$staffUser->hasRole('staff')) {
            $staffUser->assignRole('staff');
        }
        $staffUser->update(['role' => 'staff']);
        $this->command->info('Staff user created/updated.');

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('- Admin: Full access to all features');
        $this->command->info('- Staff: Can manage orders, books, and view reports');
        $this->command->info('- User: Read-only access with ability to create reviews and reservations');
    }
}