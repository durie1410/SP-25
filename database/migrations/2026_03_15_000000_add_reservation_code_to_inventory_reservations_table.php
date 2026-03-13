<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_reservations', 'reservation_code')) {
                $table->string('reservation_code', 50)->nullable()->after('reader_id');
                $table->index('reservation_code', 'idx_inventory_reservation_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_reservations', 'reservation_code')) {
                $table->dropIndex('idx_inventory_reservation_code');
                $table->dropColumn('reservation_code');
            }
        });
    }
};
