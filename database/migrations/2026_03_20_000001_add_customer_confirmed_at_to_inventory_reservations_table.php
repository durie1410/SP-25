<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_reservations', 'customer_confirmed_at')) {
                $table->timestamp('customer_confirmed_at')->nullable()->after('ready_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_reservations', 'customer_confirmed_at')) {
                $table->dropColumn('customer_confirmed_at');
            }
        });
    }
};
