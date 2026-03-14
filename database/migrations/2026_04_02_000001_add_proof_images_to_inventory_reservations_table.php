<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_reservations', 'proof_images')) {
                $table->json('proof_images')->nullable()->after('admin_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_reservations', 'proof_images')) {
                $table->dropColumn('proof_images');
            }
        });
    }
};
