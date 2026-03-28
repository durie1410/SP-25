<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (!Schema::hasColumn('borrow_items', 'return_proof_images')) {
                $table->json('return_proof_images')->nullable()->after('tinh_trang_sach_cuoi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (Schema::hasColumn('borrow_items', 'return_proof_images')) {
                $table->dropColumn('return_proof_images');
            }
        });
    }
};
