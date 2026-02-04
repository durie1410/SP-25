<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tạo staff user mẫu và gán role staff
     */
    public function up(): void
    {
        // Tìm hoặc tạo staff user
        $staff = User::firstOrCreate([
            'email' => 'staff@library.com'
        ], [
            'name' => 'Nhân viên',
            'password' => bcrypt('123456'),
            'role' => 'staff'
        ]);

        // Gán role staff
        if (!$staff->hasRole('staff')) {
            $staff->assignRole('staff');
            $staff->update(['role' => 'staff']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Đổi staff user thành user role
        $staff = User::where('email', 'staff@library.com')->first();
        if ($staff) {
            $staff->removeRole('staff');
            $staff->assignRole('user');
            $staff->update(['role' => 'user']);
        }
    }
};
