<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_reservations', 'borrow_id')) {
                $table->unsignedBigInteger('borrow_id')->nullable()->after('inventory_id');
                $table->index('borrow_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_reservations', 'borrow_id')) {
                $table->dropIndex(['borrow_id']);
                $table->dropColumn('borrow_id');
            }
        });
    }
};
