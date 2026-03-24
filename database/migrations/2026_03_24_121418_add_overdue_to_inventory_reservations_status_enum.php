<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOverdueToInventoryReservationsStatusEnum extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inventory_reservations MODIFY COLUMN status ENUM('pending', 'ready', 'fulfilled', 'cancelled', 'overdue') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inventory_reservations MODIFY COLUMN status ENUM('pending', 'ready', 'fulfilled', 'cancelled') DEFAULT 'pending'");
    }
}
