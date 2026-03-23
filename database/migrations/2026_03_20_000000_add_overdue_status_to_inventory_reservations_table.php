<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum không cho ALTER thêm giá trị trực tiếp, cần dùng procedural code
        DB::statement("ALTER TABLE inventory_reservations MODIFY COLUMN status ENUM('pending', 'ready', 'fulfilled', 'cancelled', 'overdue') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inventory_reservations MODIFY COLUMN status ENUM('pending', 'ready', 'fulfilled', 'cancelled') DEFAULT 'pending'");
    }
};
